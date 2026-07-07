<?php
namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * الشاشة الجديدة لتعديل هوية الشركة (اسم/شعار/بيانات اتصال/حالة تفعيل) —
 * حلت محل هذا الجزء من شاشة "الضبط العام" القديمة داخل HR. تكتب في مكانين
 * بنفس الوقت: admin_panel_settings (عشان كل الشاشات القديمة التي تقرأ منه
 * — الهيدر/المطبوعات — تفضل شغالة بدون تعديل) و companies (المصدر الحقيقي
 * الجديد للهوية).
 */
class CompanyProfileController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public function edit()
    {
        $setting = Admin_panel_setting::where('com_code', $this->comCode())->first();
        $company = $setting?->company_id ? Company::find($setting->company_id) : null;

        return view('admin.system.company_profile.edit', compact('setting', 'company'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'com_name' => 'required|string|max:250',
        ], [
            'com_name.required' => 'حقل اسم الشركة مطلوب',
        ]);

        $setting = Admin_panel_setting::where('com_code', $this->comCode())->first();
        if (!$setting) {
            return redirect()->route('company_profile.edit')
                ->with('errorUpdate', 'لا توجد بيانات شركة لتعديلها.');
        }

        $logoPath = $setting->image;
        $uploadedFile = $request->file('logo_file');
        if ($uploadedFile && $uploadedFile->isValid()) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $uploadedFile->store('logos', 'public');
        }

        DB::transaction(function () use ($request, $setting, $logoPath) {
            $setting->update([
                'com_name'      => $request->com_name,
                'phone'         => $request->phone ?? '',
                'email'         => $request->email ?? '',
                'address'       => $request->address ?? '',
                'saysem_status' => $request->saysem_status ?? 1,
                'image'         => $logoPath,
                'updated_by'    => Auth::guard('admin')->id(),
            ]);

            $company = $setting->company_id ? Company::find($setting->company_id) : null;
            if ($company) {
                $company->update([
                    'name'      => $request->com_name,
                    'phone'     => $request->phone ?? '',
                    'email'     => $request->email ?? '',
                    'address'   => $request->address ?? '',
                    'logo'      => $logoPath,
                    'is_active' => $request->saysem_status ?? 1,
                ]);
            }
        });

        return redirect()->route('company_profile.edit')->with('success', 'تم حفظ بيانات الشركة بنجاح');
    }
}
