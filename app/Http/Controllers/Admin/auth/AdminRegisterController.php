<?php
// ============================================================
// FILE: app/Http/Controllers/Admin/auth/AdminRegisterController.php
// ============================================================
namespace App\Http\Controllers\Admin\auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminRegisterController extends Controller
{
    public function register()
    {
        $companies = Company::where('is_active', 1)->orderBy('name')->get();
        return view('admin.auth.register', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:admins,email',
            'password'              => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string',
            'company_mode'          => 'required|in:existing,new',
            // اختيار شركة موجودة
            'company_id'            => 'required_if:company_mode,existing|nullable|exists:companies,id',
            // إنشاء شركة جديدة
            'new_company_name'      => 'required_if:company_mode,new|nullable|string|max:200',
        ], [
            'email.unique'              => 'هذا البريد الإلكتروني مسجل من قبل',
            'company_id.required_if'    => 'يرجى اختيار الشركة',
            'new_company_name.required_if' => 'يرجى إدخال اسم الشركة الجديدة',
        ]);

        DB::beginTransaction();
        try {
            $companyId = null;
            $isSuperAdmin = 0;

            if ($request->company_mode === 'new') {
                // إنشاء شركة جديدة + السوبر أدمن الأول لها
                $slug = Str::slug($request->new_company_name);
                // تجنب تكرار الـ slug
                $existingSlug = Company::where('slug', $slug)->exists();
                if ($existingSlug) {
                    $slug .= '-' . time();
                }

                $company = Company::create([
                    'name'      => $request->new_company_name,
                    'slug'      => $slug,
                    'phone'     => $request->company_phone ?? null,
                    'email'     => $request->email,
                    'is_active' => 1,
                ]);
                $companyId    = $company->id;
                $isSuperAdmin = 1; // أول مسجل في شركة جديدة = سوبر أدمن

                // إنشاء ضبط عام افتراضي للشركة
                Admin_panel_setting::create([
                    'company_id'              => $company->id,
                    'com_name'                => $request->new_company_name,
                    'saysem_status'           => 1,
                    'com_code'                => $company->id,
                    'image'                   => '',
                    'phone'                   => '',
                    'address'                 => '',
                    'after_minute_calc_delay' => 15,
                    'after_minute_calc_early' => 15,
                    'monthly_vacation_balance'=> 1.75, // 21 يوم / 12 شهر
                    'delay_calc_mode'         => 1,
                    'added_by'                => 0,
                ]);

            } else {
                $companyId = $request->company_id;
            }

            Admin::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'company_id'    => $companyId,
                'com_code'      => $companyId, // للتوافق مع الكود القديم
                'is_super_admin'=> $isSuperAdmin,
            ]);

            DB::commit();
            return redirect()->route('admin.dashboard.login')
                ->with('success', 'تم التسجيل بنجاح. يمكنك تسجيل الدخول الآن.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage())->withInput();
        }
    }
}
