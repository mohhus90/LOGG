<?php

namespace App\Services;

use App\Models\Admin_panel_setting;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private ?Admin_panel_setting $setting;

    public function __construct(int $comCode)
    {
        $this->setting = Admin_panel_setting::getByComCode($comCode);
    }

    public function isEnabled(): bool
    {
        return $this->setting && $this->setting->sms_enabled
            && $this->setting->sms_username
            && $this->setting->sms_password
            && $this->setting->sms_sender;
    }

    /**
     * إرسال رسالة SMS لرقم هاتف واحد أو مصفوفة أرقام.
     * يستخدم session-based scraping على VictoryLink BulkSMS.
     */
    public function send(string|array $phones, string $message): bool
    {
        if (!$this->isEnabled()) return false;

        $phones     = is_array($phones) ? $phones : [$phones];
        $normalized = array_values(array_filter(array_map([$this, 'normalizePhone'], $phones)));

        if (empty($normalized)) return false;

        $session = $this->authenticate();
        if (!$session) return false;

        return $this->postToVlserv($session, $normalized, $message)['sent'];
    }

    /**
     * إرسال نفس الرسالة لمجموعة موظفين فى استدعاء API واحد فقط للدفعة كلها.
     * VLServ يفرض فترة تهدئة بين عمليات إرسال الدفعات على نفس الحساب — استدعاء
     * Create عدة مرات متتالية (مرة لكل رقم) كان يفشل فيما عدا الاستدعاء الأول
     * حتى مع تسجيل دخول وجلسة واحدة (راجع memory project-vlserv-sso). الحل
     * الصحيح هو إرسال كل الأرقام دفعة واحدة كما هو مصمم فى BulkSMS API نفسه.
     *
     * $phones: مصفوفة associative [مفتاح => رقم هاتف] (المفتاح مثلاً id الموظف).
     * يعيد نفس المفاتيح مع ['sent' => bool, 'reason' => string|null].
     */
    public function sendBatch(array $phones, string $message): array
    {
        $results = [];
        foreach ($phones as $key => $phone) {
            $results[$key] = ['sent' => false, 'reason' => null];
        }
        if (!$this->isEnabled() || empty($phones)) return $results;

        $normalizedByKey = [];
        foreach ($phones as $key => $phone) {
            $n = $this->normalizePhone((string) $phone);
            if ($n !== '') $normalizedByKey[$key] = $n;
        }
        if (empty($normalizedByKey)) return $results;

        $session = $this->authenticate();
        if (!$session) return $results;

        $api = $this->postToVlserv($session, array_values($normalizedByKey), $message);
        $invalidSet = array_flip($api['invalidNumbers']);

        foreach ($normalizedByKey as $key => $n) {
            if (isset($invalidSet[$n])) {
                $results[$key] = ['sent' => false, 'reason' => 'invalid_number'];
            } else {
                $results[$key] = ['sent' => $api['sent'], 'reason' => $api['sent'] ? null : $api['message']];
            }
        }

        return $results;
    }

    private function authenticate(): ?array
    {
        try {
            $baseUrl = $this->getBaseUrl();
            $jar     = new CookieJar();
            $client  = new Client([
                'cookies'         => $jar,
                'allow_redirects' => true,
                'timeout'         => 30,
                'verify'          => false,
            ]);

            // الخطوة 1: تسجيل الدخول
            $loginOk = $this->doLogin($client, $baseUrl);
            if (!$loginOk) {
                Log::error("SmsService: VLServ login failed for '{$this->setting->sms_username}'");
                return null;
            }

            // الخطوة 2: جلب صفحة الإرسال لاستخراج CSRF token + sender ID
            $smsPage = $client->get($baseUrl . '/BulkSMS/SMS/SendSMS/Index');
            $smsHtml = (string) $smsPage->getBody();

            // إذا أعادنا إلى صفحة تسجيل الدخول → فشل التوثيق
            if (str_contains($smsHtml, 'name="Password"') || !str_contains($smsHtml, 'SendSMS')) {
                Log::error("SmsService: session not authenticated after login (redirected back to login page)");
                return null;
            }

            $csrfToken = $this->extractToken($smsHtml);
            $senderId  = $this->findSenderId($smsHtml);

            if ($csrfToken) {
                Log::info("SmsService: CSRF token found (len=" . strlen($csrfToken) . ")");
            } else {
                Log::notice("SmsService: no CSRF token on Send SMS page — proceeding without it");
            }

            if (!$senderId) {
                Log::error("SmsService: sender '{$this->setting->sms_sender}' not found in VLServ approved senders list");
                return null;
            }
            Log::info("SmsService: authenticated, using sender ID={$senderId}");

            return [
                'client'    => $client,
                'baseUrl'   => $baseUrl,
                'csrfToken' => $csrfToken,
                'senderId'  => $senderId,
            ];
        } catch (\Exception $e) {
            Log::error("SmsService: authenticate exception — " . $e->getMessage());
            return null;
        }
    }

    /**
     * @return array{sent: bool, invalidNumbers: string[], message: string}
     */
    private function postToVlserv(array $session, array $normalizedPhones, string $message): array
    {
        try {
            // بناء multipart form — CSRF token اختياري (صفحة Create لا تطلبه دائماً)
            $multipart = [
                ['name' => 'SelectedTemplateID',   'contents' => ''],
                ['name' => 'WithDLR',              'contents' => 'true'],
                ['name' => 'IsCustom',             'contents' => 'false'],
                ['name' => 'IsTemplate',           'contents' => 'false'],
                ['name' => 'TemplateName',         'contents' => ''],
                ['name' => 'SelectedFakeSenderID', 'contents' => (string) $session['senderId']],
                ['name' => 'NewFakeSender',        'contents' => ''],
                ['name' => 'CampaignCategoryId',   'contents' => ''],
                ['name' => 'CampaignCategoryName', 'contents' => ''],
                ['name' => 'PhoneNumbers',         'contents' => implode(',', $normalizedPhones)],
                ['name' => 'SMSText',              'contents' => $message],
                ['name' => 'IsSMSTemplate',        'contents' => 'True'],
                ['name' => 'AllowDuplication',     'contents' => 'true'],
                ['name' => 'SendingType',          'contents' => '2'],
                ['name' => 'SendDate',             'contents' => ''],
            ];
            if ($session['csrfToken']) {
                array_unshift($multipart, ['name' => '__RequestVerificationToken', 'contents' => $session['csrfToken']]);
            }

            // POST إلى Create — استدعاء واحد لكل الأرقام دفعة واحدة
            $response = $session['client']->post($session['baseUrl'] . '/BulkSMS/SMS/SendSMS/Create', [
                'headers' => [
                    'Referer'          => $session['baseUrl'] . '/BulkSMS/SMS/SendSMS/Index',
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'multipart' => $multipart,
            ]);

            $body = (string) $response->getBody();
            Log::info("SmsService: Create response status={$response->getStatusCode()} body=" . substr($body, 0, 500));
            $data = json_decode($body, true);

            $invalidNumbers = [];
            if (!empty($data['InvalidNumbers'])) {
                $invalidNumbers = array_values(array_filter(array_map('trim', explode(',', $data['InvalidNumbers']))));
            }

            if ($data && ($data['MessageTypeId'] ?? 0) === 1) {
                Log::info("SmsService: sent to " . count($normalizedPhones) . " number(s) — " . ($data['Message'] ?? 'OK'));
                return ['sent' => true, 'invalidNumbers' => $invalidNumbers, 'message' => $data['Message'] ?? 'OK'];
            }

            Log::warning("SmsService: send failed — " . ($data['Message'] ?? substr($body, 0, 500)));
            return ['sent' => false, 'invalidNumbers' => $invalidNumbers, 'message' => $data['Message'] ?? 'فشل الإرسال'];

        } catch (\Exception $e) {
            Log::error("SmsService: postToVlserv exception — " . $e->getMessage());
            return ['sent' => false, 'invalidNumbers' => [], 'message' => $e->getMessage()];
        }
    }

    // ─── Events ───────────────────────────────────────────────────────────────

    public function sendWelcomeEmployee(string $phone, string $name): void
    {
        if (!($this->setting->sms_on_employee_create ?? false)) return;
        $this->send($phone, "مرحباً {$name}، تم تسجيلك في منظومة الموارد البشرية بنجاح.");
    }

    public function sendPayrollApproved(string $phone, string $name, float $netSalary, int $month, int $year): void
    {
        if (!($this->setting->sms_on_payroll_approve ?? false)) return;
        $net = number_format($netSalary, 2);
        $this->send($phone, "عزيزي {$name}، تم اعتماد راتبك لشهر {$this->monthName($month)} {$year}. صافي الراتب: {$net} جنيه.");
    }

    public function sendRequestApproved(string $phone, string $name, string $requestType): void
    {
        if (!($this->setting->sms_on_request_approve ?? false)) return;
        $this->send($phone, "عزيزي {$name}، تمت الموافقة على طلب {$this->requestTypeName($requestType)} المقدم منك.");
    }

    public function sendRequestRejected(string $phone, string $name, string $requestType): void
    {
        if (!($this->setting->sms_on_request_reject ?? false)) return;
        $this->send($phone, "عزيزي {$name}، نأسف، تم رفض طلب {$this->requestTypeName($requestType)} المقدم منك. يُرجى التواصل مع الإدارة.");
    }

    public function sendAdvanceCreated(string $phone, string $name, float $amount): void
    {
        if (!($this->setting->sms_on_advance_create ?? false)) return;
        $this->send($phone, "عزيزي {$name}، تم تسجيل سلفة بمبلغ " . number_format($amount, 2) . " جنيه. سيتم خصمها على أقساط من راتبك.");
    }

    public function sendSanctionCreated(string $phone, string $name): void
    {
        if (!($this->setting->sms_on_sanction_create ?? false)) return;
        $this->send($phone, "عزيزي {$name}، تم تسجيل جزاء في سجلك الوظيفي. يُرجى التواصل مع مديرك المباشر.");
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function doLogin(Client $client, string $baseUrl): bool
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ];

        // الخطوة 1: جلب صفحة تسجيل الدخول
        $loginPage = $client->get($baseUrl . '/SSO/AccountPages/Login', ['headers' => $headers]);
        $loginHtml = (string) $loginPage->getBody();
        $token     = $this->extractToken($loginHtml);

        // الخطوة 2: POST بيانات الدخول → يُرجع SSO relay form
        $relayResp = $client->post($baseUrl . '/SSO/AccountPages/Login', [
            'headers'     => array_merge($headers, ['Referer' => $baseUrl . '/SSO/AccountPages/Login']),
            'form_params' => [
                '__RequestVerificationToken' => $token,
                'UserName'                   => $this->setting->sms_username,
                'Password'                   => $this->setting->sms_password,
                'RememberMe'                 => 'false',
            ],
        ]);
        $relayHtml = (string) $relayResp->getBody();

        // الخطوة 3: تنفيذ SSO Relay يدوياً (JavaScript auto-submit)
        if (str_contains($relayHtml, 'loginForm') || str_contains($relayHtml, 'getElementById')) {
            $relayAction = '';
            foreach ([
                '/<form[^>]+id=["\']loginForm["\'][^>]+action=["\']([^"\']+)["\'][^>]*>/i',
                '/<form[^>]+action=["\']([^"\']+)["\'][^>]+id=["\']loginForm["\'][^>]*>/i',
                '/<form[^>]+action=["\']([^"\']+)["\'][^>]*>/i',
            ] as $pat) {
                if (preg_match($pat, $relayHtml, $am)) { $relayAction = $am[1]; break; }
            }

            if ($relayAction && !str_starts_with($relayAction, 'http')) {
                $relayAction = $baseUrl . '/' . ltrim($relayAction, '/');
            }

            preg_match_all('/<input\s[^>]*>/i', $relayHtml, $inputTags);
            $relayParams = [];
            foreach ($inputTags[0] as $inp) {
                if (!preg_match('/type=["\']hidden["\']/i', $inp)) continue;
                preg_match('/\sname=["\']([^"\']+)["\']/', $inp, $nm);
                preg_match('/\svalue=["\']([^"\']*)["\']/', $inp, $vm);
                if ($nm) $relayParams[$nm[1]] = $vm[1] ?? '';
            }

            if ($relayAction && $relayParams) {
                $client->post($relayAction, [
                    'headers'     => array_merge($headers, ['Referer' => $baseUrl . '/SSO/AccountPages/Login']),
                    'form_params' => $relayParams,
                ]);
            }
        }

        return true; // الفحص الفعلي يتم في send() بعد محاولة الوصول للصفحة المحمية
    }

    private function extractToken(string $html): string
    {
        // Try common ASP.NET AntiForgeryToken patterns
        if (preg_match('/name="__RequestVerificationToken"\s+type="hidden"\s+value="([^"]+)"/', $html, $m)) return $m[1];
        if (preg_match('/type="hidden"\s+name="__RequestVerificationToken"\s+value="([^"]+)"/', $html, $m)) return $m[1];
        if (preg_match('/__RequestVerificationToken[^>]+value="([^"]+)"/', $html, $m))                      return $m[1];
        return '';
    }

    private function findSenderId(string $html): ?string
    {
        $name = $this->setting->sms_sender;

        // استخراج محتوى الـ select الخاص بالمرسلين
        preg_match('/<select[^>]+name="SelectedFakeSenderID"[^>]*>(.*?)<\/select>/si', $html, $sel);
        $opts = $sel[1] ?? $html;

        // مطابقة تامة
        if (preg_match('/<option\s+value="(\d+)">\s*' . preg_quote($name, '/') . '\s*<\/option>/i', $opts, $m)) {
            return $m[1];
        }
        // مطابقة جزئية
        if (preg_match('/<option\s+value="(\d+)">[^<]*' . preg_quote($name, '/') . '[^<]*<\/option>/i', $opts, $m)) {
            Log::info("SmsService: found sender '{$name}' with ID {$m[1]} (partial match)");
            return $m[1];
        }
        // أول مرسل متاح كاحتياط
        if (preg_match('/<option\s+value="(\d+)">[^<]+<\/option>/', $opts, $m)) {
            Log::warning("SmsService: sender '{$name}' not found, using first available sender ID {$m[1]}");
            return $m[1];
        }

        return null;
    }

    /**
     * استخراج الـ base URL من الإعداد المحفوظ.
     * يقبل أي شكل: https://smsvas.vlserv.com أو https://smsvas.vlserv.com/msg إلخ
     */
    public function getBaseUrl(): string
    {
        $p = parse_url($this->setting->sms_api_url ?? 'https://smsvas.vlserv.com');
        return ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? 'smsvas.vlserv.com');
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (!$phone) return '';
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = '20' . ltrim($phone, '0');
        }
        return $phone;
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',    4 => 'أبريل',
            5 => 'مايو',  6 => 'يونيو',  7 => 'يوليو',   8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ][$month] ?? (string) $month;
    }

    private function requestTypeName(string $type): string
    {
        return match ($type) {
            'annual_vacation' => 'إجازة اعتيادية',
            'casual_vacation' => 'إجازة عارضة',
            'late_permission' => 'إذن تأخير',
            'early_leave'     => 'إذن انصراف مبكر',
            'mission'         => 'مهمة',
            default           => $type,
        };
    }
}
