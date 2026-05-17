<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin_panel_setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminPanelSettingController extends Controller
{
    private function comCode(): int
    {
        return Auth::guard('admin')->user()->com_code;
    }

    public function index()
    {
        $data = Admin_panel_setting::where('com_code', $this->comCode())->first();
        return view('admin.PanelSetting.index', compact('data'));
    }

    public function edit()
    {
        $data = Admin_panel_setting::where('com_code', $this->comCode())->first();
        return view('admin.PanelSetting.edit', compact('data'));
    }

    public function create()
    {
        return view('admin.PanelSetting.create');
    }

    public function store(Request $request)
    {
      
        $request->validate([
            'com_name' => 'required|string|max:200',
        ]);

        $exists = Admin_panel_setting::where('com_code', $this->comCode())->exists();
        if ($exists) {
            return redirect()->route('generalsetting.edit')
                ->with('errorUpdate', 'الضبط موجود مسبقاً. استخدم صفحة التعديل.');
        }

        Admin_panel_setting::create([
            ...$request->except('_token', 'logo_file'),
            'com_code'  => $this->comCode(),
            'added_by'  => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('generalsetting.index')
            ->with('success', 'تم إنشاء الضبط بنجاح');
    }

    public function update(Request $request)
    {
        $request->validate([
            'com_name'        => 'required|string|max:200',
            'delay_calc_mode' => 'required|integer|between:1,3',
            'logo_file'       => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $data = Admin_panel_setting::where('com_code', $this->comCode())->firstOrFail();

        // ── معالجة اللوجو ──
        $logoPath = $data->image;
        if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
            // حذف اللوجو القديم
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }
            $logoPath = $request->file('logo_file')->store('logos', 'public');
        }

        $data->update([
            'com_name'                    => $request->com_name,
            'phone'                       => $request->phone,
            'email'                       => $request->email,
            'address'                     => $request->address,
            'saysem_status'               => $request->saysem_status ?? 1,
            'image'                       => $logoPath,
            // إعدادات التأخير
            'delay_calc_mode'             => $request->delay_calc_mode,
            'after_minute_calc_delay'     => $request->after_minute_calc_delay ?? 0,
            'after_minute_calc_early'     => $request->after_minute_calc_early ?? 0,
            'sanctions_value_minute_delay'=> $request->sanctions_value_minute_delay ?? 0,
            'after_time_half_daycut'      => $request->after_time_half_daycut ?? 0,
            'after_time_allday_daycut'    => $request->after_time_allday_daycut ?? 0,
            'after_minute_quarterday'     => $request->after_minute_quarterday ?? 0,
            // إعدادات الإجازات
            'annual_vacation_days'        => $request->annual_vacation_days ?? 21,
            'casual_vacation_days'        => $request->casual_vacation_days ?? 6,
            'monthly_vacation_balance'    => $request->monthly_vacation_balance ?? 1.75,
            'after_days_begain_vacation'  => $request->after_days_begain_vacation ?? 180,
            // الغياب
            'sanctions_value_first_abcence'  => $request->sanctions_value_first_abcence  ?? 1,
            'sanctions_value_second_abcence' => $request->sanctions_value_second_abcence ?? 2,
            'sanctions_value_third_abcence'  => $request->sanctions_value_third_abcence  ?? 3,
            'sanctions_value_forth_abcence'  => $request->sanctions_value_forth_abcence  ?? 4,
            // الباقي
            'updated_by' => Auth::guard('admin')->id(),
        ]);

        // تحديث اسم الشركة في جدول Companies أيضاً
        if (Auth::guard('admin')->user()->company) {
            Auth::guard('admin')->user()->company->update(['name' => $request->com_name]);
        }

        return redirect()->route('generalsetting.edit')
            ->with('success', 'تم حفظ الإعدادات بنجاح');
    }

   
}
