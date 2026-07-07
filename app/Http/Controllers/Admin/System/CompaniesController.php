<?php
namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * سجل الشركات المستأجرة (Tenants) — سوبر أدمن فقط. يعمل مباشرة على جدول
 * companies الحقيقي، ويُزامن أي تعديل هوية مع admin_panel_settings الخاص
 * بنفس com_code حتى لا تنكسر الشاشات القديمة التي تقرأ منه.
 */
class CompaniesController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        if (!Auth::guard('admin')->check() || !Auth::guard('admin')->user()->is_super_admin) {
            abort(403, 'هذه الشاشة متاحة لسوبر أدمن فقط');
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $companies = Company::orderBy('name')->paginate(20);

        return view('admin.system.companies.index', compact('companies'));
    }

    public function edit(Company $company)
    {
        $this->ensureSuperAdmin();

        return view('admin.system.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->ensureSuperAdmin();

        $request->validate([
            'name' => 'required|string|max:200',
        ], [
            'name.required' => 'حقل اسم الشركة مطلوب',
        ]);

        DB::transaction(function () use ($request, $company) {
            $company->update([
                'name'      => $request->name,
                'phone'     => $request->phone ?? '',
                'email'     => $request->email ?? '',
                'address'   => $request->address ?? '',
                'is_active' => $request->boolean('is_active'),
            ]);

            Admin_panel_setting::where('company_id', $company->id)->update([
                'com_name'      => $request->name,
                'phone'         => $request->phone ?? '',
                'email'         => $request->email ?? '',
                'address'       => $request->address ?? '',
                'saysem_status' => $request->boolean('is_active') ? 1 : 0,
            ]);
        });

        return redirect()->route('companies.index')->with('success', 'تم تحديث بيانات الشركة بنجاح');
    }
}
