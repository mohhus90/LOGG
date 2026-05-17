<?php

namespace App\Http\Controllers\Admin\auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminRegisterController extends Controller
{
    public function register()
    {
        // ✅ FIX: التحقق من وجود جدول companies قبل الاستعلام
        $companies = collect();
        if (Schema::hasTable('companies')) {
            $companies = \App\Models\Company::where('is_active', 1)->orderBy('name')->get();
        }

        return view('admin.auth.register', compact('companies'));
    }

    public function store(Request $request)
    {
        $hasCompanies = Schema::hasTable('companies');

        // ── قواعد التحقق تعتمد على وجود جدول companies ──
        $rules = [
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:admins,email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
        ];

        if ($hasCompanies) {
            $rules['company_mode']     = 'required|in:existing,new';
            $rules['company_id']       = 'required_if:company_mode,existing|nullable|exists:companies,id';
            $rules['new_company_name'] = 'required_if:company_mode,new|nullable|string|max:200';
        } else {
            // بدون نظام الشركات — com_code مطلوب
            $rules['com_code'] = 'required|integer';
        }

        $request->validate($rules, [
            'email.unique'                 => 'هذا البريد الإلكتروني مسجل من قبل',
            'company_id.required_if'       => 'يرجى اختيار الشركة',
            'new_company_name.required_if' => 'يرجى إدخال اسم الشركة الجديدة',
        ]);

        DB::beginTransaction();
        try {
            $companyId    = null;
            $comCode      = $request->com_code ?? 1;
            $isSuperAdmin = 0;

            if ($hasCompanies && $request->company_mode === 'new') {
                // ── إنشاء شركة جديدة ──
                $slug = Str::slug($request->new_company_name);
                if (\App\Models\Company::where('slug', $slug)->exists()) {
                    $slug .= '-' . time();
                }

                $company = \App\Models\Company::create([
                    'name'      => $request->new_company_name,
                    'slug'      => $slug,
                    'phone'     => $request->company_phone ?? null,
                    'email'     => $request->email,
                    'is_active' => 1,
                ]);

                $companyId    = $company->id;
                $comCode      = $company->id;
                $isSuperAdmin = 1; // أول مسجل = سوبر أدمن

                // ── إنشاء ضبط عام افتراضي للشركة ──
                $this->createDefaultSettings($company->id, $request->new_company_name, $comCode);

            } elseif ($hasCompanies && $request->company_mode === 'existing') {
                $companyId = $request->company_id;
                $comCode   = $companyId;

                // التحقق إن كانت الشركة ليس لها سوبر أدمن بعد
                $hasSuperAdmin = Admin::where('company_id', $companyId)
                    ->where('is_super_admin', 1)->exists();
                if (!$hasSuperAdmin) {
                    $isSuperAdmin = 1;
                }
            } else {
                // ── وضع legacy بدون نظام الشركات ──
                $comCode = (int)$request->com_code;

                // أول أدمن بهذا com_code يصبح سوبر أدمن
                $exists = Admin::where('com_code', $comCode)->exists();
                if (!$exists) $isSuperAdmin = 1;
            }

            // ── إنشاء الأدمن ──
            $adminData = [
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'com_code' => $comCode,
            ];

            if (Schema::hasColumn('admins', 'company_id')) {
                $adminData['company_id'] = $companyId;
            }
            if (Schema::hasColumn('admins', 'is_super_admin')) {
                $adminData['is_super_admin'] = $isSuperAdmin;
            }

            Admin::create($adminData);

            DB::commit();
            return redirect()->route('admin.dashboard.login')
                ->with('success', 'تم التسجيل بنجاح. يمكنك تسجيل الدخول الآن.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * إنشاء ضبط عام افتراضي للشركة الجديدة
     */
    private function createDefaultSettings(int $companyId, string $companyName, int $comCode): void
    {
        $data = [
            'com_name'                       => $companyName,
            'saysem_status'                  => 1,
            'phone'                          => '',
            'address'                        => '',
            'email'                          => '',
            'com_code'                       => $comCode,
            'added_by'                       => 0,
            'after_minute_calc_delay'        => 15,
            'after_minute_calc_early'        => 15,
            'after_minute_quarterday'        => 0,
            'after_time_half_daycut'         => 0,
            'after_time_allday_daycut'       => 0,
            'monthly_vacation_balance'       => 1.75,
            'first_balance_begain_vacation'  => 0,
            'after_days_begain_vacation'     => 0,
            'sanctions_value_first_abcence'  => 1,
            'sanctions_value_second_abcence' => 2,
            'sanctions_value_third_abcence'  => 3,
            'sanctions_value_forth_abcence'  => 4,
        ];

        // إضافة الحقول الجديدة إن وُجدت
        if (Schema::hasColumn('admin_panel_settings', 'company_id')) {
            $data['company_id'] = $companyId;
        }
        if (Schema::hasColumn('admin_panel_settings', 'delay_calc_mode')) {
            $data['delay_calc_mode'] = 1;
        }

        Admin_panel_setting::create($data);
    }
}