<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminPanelSettingController extends Controller
{
    /**
     * ✅ CORE FIX: com_code يُحدَّد دائماً من الأدمن — لا يأتي أبداً من الـ request
     */
    private function getComCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    /**
     * ✅ CORE FIX: البحث عن السجل بـ com_code الأدمن — ليس بـ first() العشوائية
     */
    private function getSetting(): ?Admin_panel_setting
    {
        return Admin_panel_setting::where('com_code', $this->getComCode())->first();
    }

    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────
    public function index()
    {
        $data = $this->getSetting();
        return view('admin.PanelSetting.index', ['data' => $data]);
    }

    // ─────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────
    public function create()
    {
        return view('admin.PanelSetting.create');
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'com_name' => 'required|string',
            ], [
                'com_name.required' => 'حقل اسم الشركة مطلوب',
            ]);

            // منع التكرار لنفس الشركة
            if ($this->getSetting()) {
                return redirect()->route('generalsetting.edit')
                    ->with('errorUpdate', 'الضبط موجود مسبقاً. استخدم صفحة التعديل.');
            }

            $data = [
                'added_by'                       => Auth::guard('admin')->id(),
                'com_name'                       => $request->com_name,
                'saysem_status'                  => $request->saysem_status       ?? 1,
                'phone'                          => $request->phone               ?? '',
                'address'                        => $request->address             ?? '',
                // ✅ FIX: com_code من الأدمن — لا من الـ request
                'com_code'                       => $this->getComCode(),
                'email'                          => $request->email               ?? '',
                'after_minute_calc_delay'        => $request->after_minute_calc_delay        ?? 0,
                'after_minute_calc_early'        => $request->after_minute_calc_early        ?? 0,
                'after_minute_quarterday'        => $request->after_minute_quarterday        ?? 0,
                'after_time_half_daycut'         => $request->after_time_half_daycut         ?? 0,
                'after_time_allday_daycut'       => $request->after_time_allday_daycut       ?? 0,
                'monthly_vacation_balance'       => $request->monthly_vacation_balance       ?? 1.75,
                'first_balance_begain_vacation'  => $request->first_balance_begain_vacation  ?? 0,
                'after_days_begain_vacation'     => $request->after_days_begain_vacation     ?? 0,
                'sanctions_value_first_abcence'  => $request->sanctions_value_first_abcence  ?? 1,
                'sanctions_value_second_abcence' => $request->sanctions_value_second_abcence ?? 2,
                'sanctions_value_third_abcence'  => $request->sanctions_value_third_abcence  ?? 3,
                'sanctions_value_forth_abcence'  => $request->sanctions_value_forth_abcence  ?? 4,
                // SMS
                'sms_enabled'            => $request->boolean('sms_enabled'),
                'sms_api_url'            => $request->sms_api_url            ?? 'https://smsvas.vlserv.com/msg',
                'sms_username'           => $request->sms_username           ?? '',
                'sms_password'           => $request->sms_password           ?? '',
                'sms_sender'             => $request->sms_sender             ?? '',
                'sms_on_employee_create' => $request->boolean('sms_on_employee_create'),
                'sms_on_payroll_approve' => $request->boolean('sms_on_payroll_approve'),
                'sms_on_request_approve' => $request->boolean('sms_on_request_approve'),
                'sms_on_request_reject'  => $request->boolean('sms_on_request_reject'),
                'sms_on_advance_create'  => $request->boolean('sms_on_advance_create'),
                'sms_on_sanction_create' => $request->boolean('sms_on_sanction_create'),
                'created_at'                     => now(),
                'updated_at'                     => now(),
            ];

            Admin_panel_setting::create($data);

            DB::commit();
            return redirect()->route('generalsetting.index')
                ->with('success', 'تم إضافة الشركة بنجاح');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AdminPanelSettingController@store: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ غير متوقع: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ─────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────
    public function edit(Admin_panel_setting $admin_panel_setting = null)
    {
        // ✅ FIX: البحث بـ com_code لا بـ Route Model Binding
        $data = $this->getSetting();
        return view('admin.PanelSetting.edit', ['data' => $data]);
    }

    // ─────────────────────────────────────────────
    // UPDATE — الإصلاح الجذري
    // ─────────────────────────────────────────────
    public function update(Request $request, Admin_panel_setting $admin_panel_setting = null)
    {
        try {
            // ✅ FIX 1: البحث عن السجل بـ com_code الأدمن — ليس بـ $request->id
            $setting = $this->getSetting();

            if (!$setting) {
                // إذا لم يوجد سجل، أنشئه
                return $this->store($request);
            }

            // ✅ بيانات هوية الشركة (الاسم/الهاتف/العنوان/الشعار/الحالة) انتقلت
            // لشاشة "بيانات شركتي" ضمن موديول النظام (CompanyProfileController) —
            // هذه الشاشة لم تعد تعدّلها، فتبقى كما ضبطها آخر تحديث من هناك.
            $updatedData = [
                'updated_by'                     => Auth::guard('admin')->id(),
                'com_code'                       => $this->getComCode(),
                'after_minute_calc_delay'        => $request->after_minute_calc_delay        ?? 0,
                'after_minute_calc_early'        => $request->after_minute_calc_early        ?? 0,
                'after_minute_quarterday'        => $request->after_minute_quarterday        ?? 0,
                'after_time_half_daycut'         => $request->after_time_half_daycut         ?? 0,
                'after_time_allday_daycut'       => $request->after_time_allday_daycut       ?? 0,
                'monthly_vacation_balance'       => $request->monthly_vacation_balance       ?? 1.75,
                'first_balance_begain_vacation'  => $request->first_balance_begain_vacation  ?? 0,
                'after_days_begain_vacation'     => $request->after_days_begain_vacation     ?? 0,
                'sanctions_value_first_abcence'  => $request->sanctions_value_first_abcence  ?? 1,
                'sanctions_value_second_abcence' => $request->sanctions_value_second_abcence ?? 1,
                'sanctions_value_third_abcence'  => $request->sanctions_value_third_abcence  ?? 1,
                'sanctions_value_forth_abcence'  => $request->sanctions_value_forth_abcence  ?? 1,
                'delay_calc_mode'                => $request->delay_calc_mode                ?? 1,
                'sanctions_value_minute_delay'   => $request->sanctions_value_minute_delay   ?? 0,
                'overtime_multiplier'            => $request->overtime_multiplier            ?? 1.5,
                'employee_insurance_rate'        => $request->employee_insurance_rate        ?? 11,
                'company_insurance_rate'         => $request->company_insurance_rate         ?? 18.75,
                'annual_vacation_days'           => $request->annual_vacation_days           ?? 21,
                'casual_vacation_days'           => $request->casual_vacation_days           ?? 6,
                'day_rate_divisor_type'          => (int)($request->day_rate_divisor_type    ?? 1),
                'day_rate_divisor_custom'        => $request->day_rate_divisor_custom        ?? 26,
                'hour_rate_divisor_type'         => (int)($request->hour_rate_divisor_type   ?? 1),
                'hour_rate_divisor_custom'       => $request->hour_rate_divisor_custom       ?? 8,
                'max_permissions_per_day'               => (int)($request->max_permissions_per_day  ?? 1),
                'max_permission_minutes_per_day'        => (int)($request->max_permission_minutes_per_day ?? 60),
                // وضع التأخير الهرمي
                'delay_tier1_minutes'                   => (int)($request->delay_tier1_minutes ?? 0),
                'delay_halfday_minutes'                 => (int)($request->delay_halfday_minutes ?? 0),
                'delay_fullday_minutes'                 => (int)($request->delay_fullday_minutes ?? 0),
                // حدود الانصراف المبكر
                'early_departure_halfday_minutes'       => (int)($request->early_departure_halfday_minutes ?? 0),
                'early_departure_fullday_minutes'       => (int)($request->early_departure_fullday_minutes ?? 0),
                'early_departure_fullplushalf_minutes'  => (int)($request->early_departure_fullplushalf_minutes ?? 0),
                // SMS
                'sms_enabled'            => $request->boolean('sms_enabled'),
                'sms_api_url'            => $request->sms_api_url            ?? ($setting->sms_api_url ?? 'https://smsvas.vlserv.com/msg'),
                'sms_username'           => $request->sms_username           ?? $setting->sms_username,
                'sms_password'           => $request->filled('sms_password') ? $request->sms_password : $setting->sms_password,
                'sms_sender'             => $request->sms_sender             ?? $setting->sms_sender,
                'sms_on_employee_create' => $request->boolean('sms_on_employee_create'),
                'sms_on_payroll_approve' => $request->boolean('sms_on_payroll_approve'),
                'sms_on_request_approve' => $request->boolean('sms_on_request_approve'),
                'sms_on_request_reject'  => $request->boolean('sms_on_request_reject'),
                'sms_on_advance_create'  => $request->boolean('sms_on_advance_create'),
                'sms_on_sanction_create' => $request->boolean('sms_on_sanction_create'),
            ];

            Admin_panel_setting::where('com_code', $this->getComCode())
                ->update($updatedData);

            return redirect()->route('generalsetting.index')
                ->with('success', 'تم تحديث البيانات بنجاح');

        } catch (\Exception $ex) {
            Log::error('AdminPanelSettingController@update: ' . $ex->getMessage());
            return redirect()->back()
                ->with('errorUpdate', 'حدث خطأ أثناء التحديث: ' . $ex->getMessage());
        }
    }

    public function show(Admin_panel_setting $admin_panel_setting)
    {
        //
    }

    public function destroy(Admin_panel_setting $admin_panel_setting)
    {
        //
    }

    public function testSms(Request $request)
    {
        $apiUrl   = $request->sms_api_url ?: 'https://smsvas.vlserv.com';
        $username = $request->sms_username;
        $password = $request->sms_password;
        $sender   = $request->sms_sender;

        if (!$username) {
            return response()->json(['success' => false, 'message' => 'اسم المستخدم مطلوب']);
        }
        if (!$password) {
            $password = $this->getSetting()->sms_password ?? '';
        }
        if (!$password) {
            return response()->json(['success' => false, 'message' => 'كلمة المرور مطلوبة']);
        }

        $p       = parse_url($apiUrl);
        $baseUrl = ($p['scheme'] ?? 'https') . '://' . ($p['host'] ?? 'smsvas.vlserv.com');

        try {
            $jar    = new \GuzzleHttp\Cookie\CookieJar();
            $client = new \GuzzleHttp\Client([
                'cookies'         => $jar,
                'allow_redirects' => true,
                'timeout'         => 20,
                'verify'          => false,
            ]);

            $loginPage = $client->get($baseUrl . '/SSO/AccountPages/Login');
            $loginHtml = (string) $loginPage->getBody();

            // استخراج CSRF token بعدة أنماط
            $token = '';
            foreach ([
                '/name="__RequestVerificationToken"\s+type="hidden"\s+value="([^"]+)"/',
                '/type="hidden"\s+name="__RequestVerificationToken"\s+value="([^"]+)"/',
                '/__RequestVerificationToken[^>]+value="([^"]+)"/',
                '/value="([^"]+)"[^>]*name="__RequestVerificationToken"/',
            ] as $pattern) {
                if (preg_match($pattern, $loginHtml, $m)) { $token = $m[1]; break; }
            }
            $browserHeaders = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Referer'    => $baseUrl . '/SSO/AccountPages/Login',
            ];

            // ── الخطوة 2: POST تسجيل الدخول → يُرجع relay form ──
            $loginResp    = $client->post($baseUrl . '/SSO/AccountPages/Login', [
                'headers'     => $browserHeaders,
                'form_params' => [
                    '__RequestVerificationToken' => $token,
                    'UserName'                   => $username,
                    'Password'                   => $password,
                    'RememberMe'                 => 'false',
                ],
            ]);
            $relayHtml = (string) $loginResp->getBody();

            // ── الخطوة 3: تنفيذ SSO Relay (JavaScript form auto-submit) ──
            if (str_contains($relayHtml, 'loginForm') || str_contains($relayHtml, 'getElementById')) {
                // استخراج form action
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

                // استخراج جميع hidden fields
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
                        'headers'     => array_merge($browserHeaders, ['Referer' => $baseUrl . '/SSO/AccountPages/Login']),
                        'form_params' => $relayParams,
                    ]);
                }
            }

            // ── الخطوة 4: فحص صفحة Send SMS ──
            $smsPage = $client->get($baseUrl . '/BulkSMS/SMS/SendSMS/Index');
            $smsHtml = (string) $smsPage->getBody();

            $onLoginPage = str_contains($smsHtml, 'name="UserName"') || str_contains($smsHtml, 'name="Password"');
            $onSmsPage   = str_contains($smsHtml, 'txtPhoneNumbers') || str_contains($smsHtml, 'baseControllerUrl');

            if ($onLoginPage || !$onSmsPage) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ فشل تسجيل الدخول — يرجى التحقق من بيانات الدخول أو التواصل مع VLServ.',
                ]);
            }

            // استخراج قائمة المرسلين
            preg_match('/<select[^>]+name="SelectedFakeSenderID"[^>]*>(.*?)<\/select>/si', $smsHtml, $sel);
            preg_match_all('/<option\s+value="(\d+)">([^<]+)<\/option>/i', $sel[1] ?? '', $senderMatches);

            $senders     = [];
            $senderFound = false;
            for ($i = 0; $i < count($senderMatches[1]); $i++) {
                $id   = trim($senderMatches[1][$i]);
                $name = trim($senderMatches[2][$i]);
                $senders[] = "{$name} (ID: {$id})";
                if ($sender && (stripos($name, $sender) !== false || stripos($sender, $name) !== false)) {
                    $senderFound = true;
                }
            }

            $senderList = $senders ? implode(' | ', $senders) : 'لا توجد مرسلون مسجّلون';

            if ($sender && !$senderFound) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ تسجيل الدخول ناجح لكن المرسل \"{$sender}\" غير موجود.\n\nالمرسلون المتاحون: {$senderList}",
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "✅ تسجيل الدخول ناجح! المرسلون المعتمدون: {$senderList}",
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ خطأ في الاتصال: ' . $e->getMessage()]);
        }

    }
}
