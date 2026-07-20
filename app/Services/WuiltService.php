<?php

namespace App\Services;

use App\Models\EcommerceStore;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * عميل GraphQL خام للتعامل مع متجر Wuilt (https://store-docs.wuilt.com).
 *
 * ملاحظة: أسماء الحقول هنا مبنية على عيّنة موجزة من توثيق Wuilt العام.
 * لازم تُراجَع مقابل الـ GraphQL schema الفعلي (introspection) بمجرد توفر
 * API Key حقيقي قبل الاعتماد النهائي عليها.
 */
class WuiltService
{
    private const ENDPOINT = 'https://graphql.wuilt.com';

    private Client $http;
    private EcommerceStore $store;

    public function __construct(EcommerceStore $store)
    {
        $this->store = $store;
        $this->http  = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'x-api-key'    => $store->api_key,
            ],
        ]);
    }

    /**
     * @return array{nodes: array, totalCount: int}
     */
    public function fetchOrders(\DateTimeInterface $from, \DateTimeInterface $to, int $first = 50, int $offset = 0): array
    {
        // أسماء الحقول تم التحقق منها فعلياً مقابل الـ API الحي (introspection معطّل عند Wuilt،
        // فتم اكتشافها بالتجربة والخطأ). لا يوجد حقل SKU على المنتج — المطابقة تتم بـ productSnapshot.id.
        $query = <<<'GRAPHQL'
            query ListStoreOrders(
                $storeId: ID!
                $connection: OrdersConnectionInput
                $filter: OrdersFilterInput
            ) {
                orders(storeId: $storeId, connection: $connection, filter: $filter) {
                    totalCount
                    nodes {
                        id
                        orderSerial
                        createdAt
                        customer { name email phone }
                        paymentStatus
                        paymentMethod
                        paidAmount { amount currencyCode }
                        fulfillmentStatus
                        shippingStatus
                        shippingRateName
                        shippingDetails {
                            orderTrackingNumber
                            trackingURL
                            airWayBill
                            shippingStatus
                        }
                        receipt {
                            subtotal { amount currencyCode }
                            discount { amount currencyCode }
                            tax { amount currencyCode }
                            shipping { amount currencyCode }
                            total { amount currencyCode }
                        }
                        items {
                            id
                            quantity
                            title
                            price { amount currencyCode }
                            productSnapshot { id title images { src } }
                        }
                        shippingAddress { addressLine1 addressLine2 postalCode phone notes }
                        orderHistory { id eventType timestamp }
                    }
                }
            }
            GRAPHQL;

        $variables = [
            'storeId' => $this->store->store_id,
            'connection' => [
                'first'     => $first,
                'offset'    => $offset,
                'sortBy'    => 'createdAt',
                'sortOrder' => 'asc',
            ],
            'filter' => [
                'isArchived' => false,
                'date' => [
                    'from' => $from->format('Y-m-d\TH:i:s.v\Z'),
                    'to'   => $to->format('Y-m-d\TH:i:s.v\Z'),
                ],
            ],
        ];

        $body = $this->request($query, $variables);
        $orders = $body['data']['orders'] ?? null;

        if ($orders === null) {
            throw new \RuntimeException('استجابة Wuilt لا تحتوي على بيانات الطلبات المتوقعة');
        }

        return [
            'nodes'      => $orders['nodes'] ?? [],
            'totalCount' => (int) ($orders['totalCount'] ?? 0),
        ];
    }

    /**
     * @return array{nodes: array, totalCount: int}
     */
    public function fetchProducts(int $first = 50, int $offset = 0): array
    {
        $query = <<<'GRAPHQL'
            query ListStoreProducts($filter: ProductsFilterInput, $connection: ProductsConnectionInput) {
                products(filter: $filter, connection: $connection) {
                    totalCount
                    nodes {
                        id
                        title
                        variants {
                            nodes {
                                id
                                title
                                sku
                                quantity
                                price { amount currencyCode }
                                cost { amount currencyCode }
                                selectedOptions {
                                    option { name }
                                    value { name }
                                }
                            }
                        }
                    }
                }
            }
            GRAPHQL;

        $variables = [
            'filter'     => ['storeIds' => [$this->store->store_id]],
            'connection' => ['first' => $first, 'offset' => $offset],
        ];

        $body = $this->request($query, $variables);
        $products = $body['data']['products'] ?? null;

        if ($products === null) {
            throw new \RuntimeException('استجابة Wuilt لا تحتوي على بيانات المنتجات المتوقعة');
        }

        return [
            'nodes'      => $products['nodes'] ?? [],
            'totalCount' => (int) ($products['totalCount'] ?? 0),
        ];
    }

    /**
     * رصيد المحفظة الحقيقي فقط (متاح ومؤكد). تفاصيل الحركات (wallet.transactions)
     * موجودة بالاسم الصحيح في الـ schema لكنها ترجع null دائماً حالياً — لسه غير مفعّلة من Wuilt.
     */
    public function fetchWalletBalance(): float
    {
        $query = <<<'GRAPHQL'
            query StoreWalletBalance($id: ID!) {
                store(id: $id) {
                    id
                    wallet { balance }
                }
            }
            GRAPHQL;

        $body = $this->request($query, ['id' => $this->store->store_id]);
        $wallet = $body['data']['store']['wallet'] ?? null;

        if ($wallet === null) {
            throw new \RuntimeException('استجابة Wuilt لا تحتوي على بيانات المحفظة المتوقعة');
        }

        return (float) ($wallet['balance'] ?? 0);
    }

    private function request(string $query, array $variables): array
    {
        try {
            $response = $this->http->post(self::ENDPOINT, [
                'json' => ['query' => $query, 'variables' => $variables],
            ]);
        } catch (RequestException $e) {
            $this->throwWuiltError($e);
        }

        $body = json_decode((string) $response->getBody(), true);

        if (!empty($body['errors'])) {
            $messages = array_map(fn ($e) => $e['message'] ?? 'خطأ غير معروف', $body['errors']);
            $detail   = implode(' | ', $messages);
            Log::error("Wuilt GraphQL error (store #{$this->store->id}): {$detail}");
            throw new \RuntimeException("فشل طلب Wuilt: {$detail}");
        }

        return $body;
    }

    private function throwWuiltError(RequestException $e): never
    {
        $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
        $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : '';

        Log::error("Wuilt HTTP error (store #{$this->store->id}) [{$code}]: {$body}");

        $hint = match (true) {
            $code === 401 => 'API Key غير صحيح أو منتهي الصلاحية',
            $code === 403 => 'غير مصرّح بالوصول لهذا المتجر — تحقق من Store ID',
            $code === 404 => 'الـ endpoint غير موجود (404)',
            $code === 0   => 'تعذّر الاتصال بسيرفر Wuilt (انتهت المهلة أو لا يوجد اتصال إنترنت)',
            default       => "خطأ HTTP: {$code}",
        };

        throw new \RuntimeException($hint . ($body !== '' ? "\n{$body}" : ''));
    }
}
