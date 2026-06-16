<?php

namespace App\Services;

use App\Models\EtaCredential;
use App\Models\EtaInvoice;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class EtaService
{
    private const TOKEN_URL = 'https://id.eta.gov.eg/connect/token';

    // البوابة الإلكترونية (ROPC — إيميل + باسوورد)
    private const PORTAL_API_BASE  = 'https://invoicing.eta.gov.eg/api/v1';
    private const PORTAL_CLIENT_ID = '9A029E3B-7403-4B25-8850-AB67E1FD92AB';
    private const PORTAL_SCOPE     = 'openid profile publicportals.bff.api';

    // API للمطورين (client_credentials — client_id + client_secret)
    private const DEV_API_BASE = 'https://api.invoicing.eta.gov.eg/api/v1';

    private const PAGE_SIZE = 100;

    private Client $http;
    private EtaCredential $credential;

    public function __construct(EtaCredential $credential)
    {
        $this->credential = $credential;
        $this->http       = new Client([
            'timeout' => 30,
            'verify'  => false,
            'headers' => [
                'Accept'          => 'application/json',
                'Accept-Language' => 'ar',
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  API Base URL (حسب نوع المصادقة)
    // ─────────────────────────────────────────────

    private function apiBase(): string
    {
        return $this->credential->auth_type === 'api'
            ? self::DEV_API_BASE
            : self::PORTAL_API_BASE;
    }

    // ─────────────────────────────────────────────
    //  Authentication
    // ─────────────────────────────────────────────

    public function getAccessToken(): string
    {
        if ($this->credential->isTokenValid()) {
            return $this->credential->access_token;
        }

        if (empty($this->credential->client_secret)) {
            throw new \RuntimeException(
                $this->credential->auth_type === 'portal'
                    ? 'كلمة المرور غير محفوظة — يرجى إعادة إدخالها في صفحة الإعداد'
                    : 'Client Secret غير محفوظ — يرجى إعادة إدخاله في صفحة الإعداد'
            );
        }

        $params = $this->credential->auth_type === 'portal'
            ? $this->portalTokenParams()
            : $this->apiTokenParams();

        try {
            $response = $this->http->post(self::TOKEN_URL, [
                'form_params' => $params,
                'headers'     => ['Content-Type' => 'application/x-www-form-urlencoded'],
            ]);
        } catch (RequestException $e) {
            $this->throwEtaError($e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (empty($data['access_token'])) {
            throw new \RuntimeException('لم يتم إرجاع access_token — تحقق من البيانات');
        }

        $this->credential->update([
            'access_token'     => $data['access_token'],
            'token_expires_at' => now()->addSeconds(($data['expires_in'] ?? 3600) - 60),
        ]);

        return $data['access_token'];
    }

    private function portalTokenParams(): array
    {
        return [
            'grant_type' => 'password',
            'username'   => trim($this->credential->client_id),   // الإيميل
            'password'   => $this->credential->client_secret,      // كلمة المرور
            'client_id'  => self::PORTAL_CLIENT_ID,
            'scope'      => self::PORTAL_SCOPE,
        ];
    }

    private function apiTokenParams(): array
    {
        return [
            'grant_type'    => 'client_credentials',
            'client_id'     => trim($this->credential->client_id),
            'client_secret' => $this->credential->client_secret,
        ];
    }

    // ─────────────────────────────────────────────
    //  Fetch & Sync
    // ─────────────────────────────────────────────

    public function syncInvoices(string $direction, ?string $from = null, ?string $to = null, string $dateType = 'issue'): array
    {
        $token = $this->getAccessToken();
        $stats = ['new' => 0, 'updated' => 0, 'errors' => 0, 'error_details' => []];

        $rangeStart = \Carbon\Carbon::parse($from ?? now()->startOfMonth()->format('Y-m-d'))->startOfDay();
        $rangeEnd   = \Carbon\Carbon::parse($to   ?? now()->format('Y-m-d'))->endOfDay();

        // ETA API: الفرق بين From وTo يجب ألا يتجاوز 31 يوماً — نقسّم تلقائياً
        $chunkStart = $rangeStart->copy();

        while ($chunkStart->lte($rangeEnd)) {
            $chunkEnd = $chunkStart->copy()->addDays(30);
            if ($chunkEnd->gt($rangeEnd)) {
                $chunkEnd = $rangeEnd->copy();
            }

            $pageNo     = 1;
            $totalPages = 1;

            do {
                if ($dateType === 'submission') {
                    $dateParams = [
                        'submissionDateFrom' => $chunkStart->format('Y-m-d'),
                        'submissionDateTo'   => $chunkEnd->format('Y-m-d'),
                    ];
                } else {
                    $dateParams = [
                        'issueDateFrom' => $chunkStart->format('Y-m-d'),
                        'issueDateTo'   => $chunkEnd->format('Y-m-d'),
                    ];
                }

                $params = array_merge([
                    'pageSize'  => self::PAGE_SIZE,
                    'pageNo'    => $pageNo,
                    'direction' => $direction,
                ], $dateParams);

                try {
                    $url      = $this->apiBase() . '/documents/search';
                    $response = $this->http->get($url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $token,
                            'Accept'        => 'application/json',
                        ],
                        'query' => $params,
                    ]);

                    $body       = json_decode((string) $response->getBody(), true);
                    $documents  = $body['result'] ?? [];
                    $totalPages = $body['metadata']['totalPages'] ?? 1;

                    $totalCount = $body['metadata']['totalCount'] ?? $body['metadata']['total'] ?? '?';
                    Log::info("ETA sync [{$direction}] {$chunkStart->format('Y-m-d')}→{$chunkEnd->format('Y-m-d')} page={$pageNo}/{$totalPages} docs=" . count($documents) . " total={$totalCount} keys=" . implode(',', array_keys($body ?? [])));
                    if (count($documents) === 0) {
                        Log::debug('ETA sync zero-result body: ' . json_encode($body, JSON_UNESCAPED_UNICODE));
                    }

                    foreach ($documents as $doc) {
                        $result = $this->upsertInvoice($doc, $direction);
                        $stats[$result]++;
                    }

                } catch (RequestException $e) {
                    $code    = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
                    $rawBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : 'لا استجابة';
                    $decoded = json_decode($rawBody, true) ?? [];
                    $toStr   = static fn ($v): string => is_array($v)
                        ? json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                        : (string) ($v ?? '');

                    $detail = "[{$code}] ";
                    if (!empty($decoded['message'])) {
                        $detail .= $toStr($decoded['message']);
                    } elseif (!empty($decoded['error'])) {
                        $detail .= $toStr($decoded['error']);
                        if (!empty($decoded['error_description'])) {
                            $detail .= ': ' . $toStr($decoded['error_description']);
                        }
                    } elseif (!empty($decoded['supportID'])) {
                        $detail .= 'supportID: ' . $toStr($decoded['supportID']);
                    } else {
                        $detail .= $rawBody;
                    }

                    $range = $chunkStart->format('Y-m-d') . ' → ' . $chunkEnd->format('Y-m-d');
                    Log::error("ETA sync error (direction={$direction}, {$range}): {$detail}");
                    $stats['errors']++;
                    $stats['error_details'][] = "اتجاه {$direction} ({$range}): {$detail}";
                    break; // تخطّى باقي الصفحات لهذا الجزء
                }

                $pageNo++;
            } while ($pageNo <= $totalPages);

            $chunkStart = $chunkEnd->copy()->addDay()->startOfDay();
        }

        return $stats;
    }

    /**
     * اختبار الوصول إلى الـ API بعد المصادقة
     */
    public function testApiAccess(): array
    {
        $token = $this->getAccessToken();
        $url   = $this->apiBase() . '/documents/search';

        try {
            $response = $this->http->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept'        => 'application/json',
                ],
                'query' => [
                    'pageSize'      => 1,
                    'pageNo'        => 1,
                    'issueDateFrom' => now()->subDays(30)->format('Y-m-d'),
                    'issueDateTo'   => now()->format('Y-m-d'),
                ],
            ]);

            $code = $response->getStatusCode();
            $body = json_decode((string) $response->getBody(), true);

            return [
                'success'    => true,
                'http_code'  => $code,
                'total'      => (int) ($body['metadata']['totalCount'] ?? 0),
                'api_url'    => $url,
                'auth_type'  => $this->credential->auth_type,
            ];
        } catch (RequestException $e) {
            $code    = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $rawBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : '';
            return [
                'success'   => false,
                'http_code' => $code,
                'raw_body'  => $rawBody,
                'api_url'   => $url,
                'auth_type' => $this->credential->auth_type,
            ];
        }
    }

    private function upsertInvoice(array $doc, string $direction): string
    {
        $existing = EtaInvoice::where('uuid', $doc['uuid'])->first();

        $data = [
            'com_code'             => $this->credential->com_code,
            'direction'            => $direction,
            'uuid'                 => $doc['uuid'],
            'long_id'              => $doc['longId']               ?? null,
            'internal_id'          => $doc['internalId']           ?? null,
            'document_type'        => $this->normalizeDocType($doc['typeName'] ?? 'I'),
            'document_type_version' => $doc['typeVersionName']     ?? null,
            'issuer_id'            => $doc['issuerId']             ?? null,
            'issuer_name'          => $doc['issuerName']           ?? null,
            'receiver_id'          => $doc['receiverId']           ?? null,
            'receiver_name'        => $doc['receiverName']         ?? null,
            'date_issued'          => isset($doc['dateTimeIssued'])
                                        ? \Carbon\Carbon::parse($doc['dateTimeIssued']) : null,
            'date_received'        => isset($doc['dateTimeReceived'])
                                        ? \Carbon\Carbon::parse($doc['dateTimeReceived']) : null,
            'total_sales'          => $doc['totalSales']           ?? 0,
            'total_discount'       => $doc['totalDiscount']        ?? 0,
            'net_amount'           => $doc['netAmount']            ?? 0,
            'total_vat'            => ($doc['total'] ?? 0) - ($doc['netAmount'] ?? 0),
            'total_amount'         => $doc['total']                ?? 0,
            'status'               => $doc['status']               ?? 'Valid',
            'activity_code'        => $doc['taxpayerActivityCode'] ?? null,
            'raw_data'             => $doc,
        ];

        if ($existing) {
            if (!$existing->is_posted) {
                $existing->update($data);
            }
            return 'updated';
        }

        EtaInvoice::create($data);
        return 'new';
    }

    public function fetchInvoiceDetails(EtaInvoice $invoice): bool
    {
        try {
            $token   = $this->getAccessToken();
            $headers = ['Authorization' => 'Bearer ' . $token];
            $base    = $this->apiBase();
            $uuid    = $invoice->uuid;

            // جرّب /details أولاً (Dev API)، ثم /raw، ثم بدون suffix (Portal API)
            $tried = [];
            $doc   = null;
            foreach (['/documents/' . $uuid . '/details', '/documents/' . $uuid . '/raw', '/documents/' . $uuid] as $path) {
                try {
                    $response = $this->http->get($base . $path, ['headers' => $headers]);
                    $body     = json_decode((string) $response->getBody(), true);
                    // تأكد أن الرد يحتوي بيانات الفاتورة
                    if (!empty($body['invoiceLines']) || !empty($body['issuer'])) {
                        $doc = $body;
                        break;
                    }
                    $tried[] = $path . ' (استجاب لكن بدون invoiceLines)';
                } catch (\GuzzleHttp\Exception\ClientException $ex) {
                    $tried[] = $path . ' → ' . $ex->getResponse()->getStatusCode();
                    continue;
                }
            }

            if ($doc === null) {
                throw new \RuntimeException('لم يُعثر على تفاصيل الفاتورة. Endpoints جُرِّبت: ' . implode(' | ', $tried));
            }

            $invoice->items()->delete();
            foreach ($doc['invoiceLines'] ?? [] as $line) {
                $taxableItems = $line['taxableItems'] ?? [];
                $taxAmount    = collect($taxableItems)->sum('amount');
                // أخذ نسبة أول ضريبة موجودة (T1/T2) وإن لم توجد نستخدم 0
                $firstTax     = collect($taxableItems)->first(fn($t) => in_array($t['taxType'] ?? '', ['T1','T2']));
                $vatRate      = $firstTax['rate'] ?? (count($taxableItems) ? ($taxableItems[0]['rate'] ?? 0) : 0);

                $invoice->items()->create([
                    'item_code'      => $line['itemCode']               ?? null,
                    'description'    => $line['description']            ?? null,
                    'unit_type'      => $line['unitType']               ?? null,
                    'quantity'       => $line['quantity']               ?? 1,
                    'unit_price'     => $line['unitValue']['amountEGP'] ?? 0,
                    'total'          => $line['salesTotal']             ?? 0,
                    'discount'       => $line['discount']['amount']     ?? 0,
                    'net_total'      => $line['netTotal']               ?? 0,
                    'vat_rate'       => $vatRate,
                    'vat_amount'     => $taxAmount,
                    'total_with_vat' => $line['total']                  ?? (($line['netTotal'] ?? 0) + $taxAmount),
                ]);
            }

            $invoice->update(['raw_data' => $doc]);
            return true;

        } catch (\Exception $e) {
            Log::error('ETA fetch details error: ' . $e->getMessage());
            throw $e;
        }
    }

    // ─────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────

    private function normalizeDocType(string $typeName): string
    {
        return match(strtolower($typeName)) {
            'creditnote', 'c' => 'C',
            'debitnote',  'd' => 'D',
            default           => 'I',
        };
    }

    /**
     * جلب قدرات السيرفر (grant_types المدعومة)
     */
    public function fetchServerCapabilities(): array
    {
        try {
            $response = $this->http->get('https://id.eta.gov.eg/.well-known/openid-configuration');
            $config   = json_decode((string) $response->getBody(), true);
            return [
                'grant_types_supported' => $config['grant_types_supported'] ?? [],
                'scopes_supported'      => $config['scopes_supported']      ?? [],
                'token_endpoint'        => $config['token_endpoint']        ?? '',
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    private function throwEtaError(RequestException $e): never
    {
        $code = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
        $body = $e->hasResponse() ? (string) $e->getResponse()->getBody() : '';

        Log::error("ETA token error [{$code}]: {$body}");

        $decoded = json_decode($body, true) ?? [];

        // تحويل آمن — قيم JSON قد تكون arrays أو nested arrays
        $toStr = static fn ($v): string => is_array($v)
            ? json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : (string) ($v ?? '');

        $etaErr    = $toStr($decoded['error']             ?? null);
        $etaDesc   = $toStr($decoded['error_description'] ?? null);
        $supportId = $toStr($decoded['supportID']         ?? null);

        // بناء رسالة واضحة
        $hint = match(true) {
            $etaErr === 'unsupported_grant_type'
                => "نوع المصادقة [{$this->grantType()}] غير مدعوم من سيرفر ETA.\n"
                 . "→ يجب استخدام Client ID + Client Secret من صفحة تكامل ERP في البوابة",
            $etaErr === 'invalid_client'
                => "Client ID غير مسجّل في النظام — تأكد من نسخ الـ Client ID بشكل صحيح",
            in_array($etaErr, ['invalid_grant', 'invalid_username_or_password'], true)
                => "الإيميل أو كلمة المرور غير صحيحة",
            $supportId !== ''
                => "رفض ETA الطلب (supportID: {$supportId})\n"
                 . "→ الطريقة الصحيحة: احصل على Client ID + Client Secret من البوابة (شرح أدناه)",
            default
                => "كود: {$code}" . ($etaErr !== '' ? " | الخطأ: {$etaErr}" : '') . ($body !== '' ? "\n{$body}" : ''),
        };

        if ($etaDesc !== '') {
            $hint .= "\n({$etaDesc})";
        }

        throw new \RuntimeException($hint);
    }

    private function grantType(): string
    {
        return $this->credential->auth_type === 'portal' ? 'password' : 'client_credentials';
    }
}
