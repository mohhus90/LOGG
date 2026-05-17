<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Admin_panel_setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminPanelSettingController extends Controller
{
    // ─────────────────────────────────────────────
    // مساعد: com_code الأدمن الحالي
    // ─────────────────────────────────────────────
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    // ─────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────
    public function index()
    {
        $data = Admin_panel_setting::where('com_code', $this->comCode())->first();
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
                'com_name' => 'required|string|max:250',
            ], [
                'com_name.required' => 'حقل اسم الشركة مطلوب',
            ]);

            // منع التكرار لنفس الشركة
            if (Admin_panel_setting::where('com_code', $this->comCode())->exists()) {
                return redirect()->route('generalsetting.edit')
                    ->with('errorUpdate', 'الضبط موجود مسبقاً. استخدم صفحة التعديل.');
            }

            Admin_panel_setting::create([
                'added_by'                       => Auth::guard('admin')->id(),
                'com_name'                       => $request->com_name,
                'saysem_status'                  => $request->saysem_status ?? 1,
                'phone'                          => $request->phone ?? '',
                'address'                        => $request->address ?? '',
                // ✅ FIX: com_code يُأخذ من الأدمن وليس من الـ request
                'com_code'                       => $this->comCode(),
                'email'                          => $request->email ?? '',
                'after_minute_calc_delay'        => $request->after_minute_calc_delay ?? 0,
                'after_minute_calc_early'        => $request->after_minute_calc_early ?? 0,
                'after_minute_quarterday'        => $request->after_minute_quarterday ?? 0,
                'after_time_half_daycut'         => $request->after_time_half_daycut ?? 0,
                'after_time_allday_daycut'       => $request->after_time_allday_daycut ?? 0,
                'monthly_vacation_balance'       => $request->monthly_vacation_balance ?? 1.75,
                'first_balance_begain_vacation'  => $request->first_balance_begain_vacation ?? 0,
                'after_days_begain_vacation'     => $request->after_days_begain_vacation ?? 0,
                'sanctions_value_first_abcence'  => $request->sanctions_value_first_abcence ?? 0,
                'sanctions_value_second_abcence' => $request->sanctions_value_second_abcence ?? 0,
                'sanctions_value_third_abcence'  => $request->sanctions_value_third_abcence ?? 0,
                'sanctions_value_forth_abcence'  => $request->sanctions_value_forth_abcence ?? 0,
                'created_at'                     => now(),
                'updated_at'                     => now(),
            ]);

            DB::commit();
            return redirect()->route('generalsetting.index')
                ->with('success', 'تم إضافة بيانات الشركة بنجاح');

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during panelSetting save: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'حدث خطأ غير متوقع: ' . $e->getMessage())
                ->withInput();
        }
    }

    // ─────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────
    public function edit()
    {
        $data = Admin_panel_setting::where('com_code', $this->comCode())->first();
        return view('admin.PanelSetting.edit', ['data' => $data]);
    }

    // ─────────────────────────────────────────────
    // UPDATE — ✅ الإصلاح الرئيسي: com_code من الأدمن لا من الـ request
    // ─────────────────────────────────────────────
    public function update(Request $request, Admin_panel_setting $admin_panel_setting)
    {
        try {
            // البحث عن السجل بـ com_code الأدمن (لا بـ route model binding)
            $setting = Admin_panel_setting::where('com_code', $this->comCode())->first();

            // إذا لم يوجد سجل، أنشئه
            if (!$setting) {
                $setting = new Admin_panel_setting();
                $setting->added_by = Auth::guard('admin')->id();
                $setting->com_code = $this->comCode(); // ✅ من الأدمن
            }

            // ── معالجة اللوجو ──
            $logoPath = $setting->image ?? null;
            if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
                if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }
                $logoPath = $request->file('logo_file')->store('logos', 'public');
            }

            $setting->updated_by                    = Auth::guard('admin')->id();
            $setting->com_name                      = $request->com_name ?? $setting->com_name;
            // ✅ FIX: لا تأخذ com_code من الـ request أبداً — تأخذه من الأدمن
            $setting->com_code                      = $this->comCode();
            $setting->saysem_status                 = $request->saysem_status ?? 1;
            $setting->phone                         = $request->phone ?? '';
            $setting->email                         = $request->email ?? '';
            $setting->address                       = $request->address ?? '';
            $setting->image                         = $logoPath;
            $setting->after_minute_calc_delay       = $request->after_minute_calc_delay ?? 0;
            $setting->after_minute_calc_early       = $request->after_minute_calc_early ?? 0;
            $setting->after_minute_quarterday       = $request->after_minute_quarterday ?? 0;
            $setting->after_time_half_daycut        = $request->after_time_half_daycut ?? 0;
            $setting->after_time_allday_daycut      = $request->after_time_allday_daycut ?? 0;
            $setting->monthly_vacation_balance      = $request->monthly_vacation_balance ?? 1.75;
            $setting->first_balance_begain_vacation = $request->first_balance_begain_vacation ?? 0;
            $setting->after_days_begain_vacation    = $request->after_days_begain_vacation ?? 0;
            $setting->sanctions_value_first_abcence  = $request->sanctions_value_first_abcence ?? 0;
            $setting->sanctions_value_second_abcence = $request->sanctions_value_second_abcence ?? 0;
            $setting->sanctions_value_third_abcence  = $request->sanctions_value_third_abcence ?? 0;
            $setting->sanctions_value_forth_abcence  = $request->sanctions_value_forth_abcence ?? 0;

            // حقول جديدة (إن وُجدت في قاعدة البيانات)
            if ($request->filled('delay_calc_mode')) {
                $setting->delay_calc_mode = $request->delay_calc_mode;
            }
            if ($request->filled('sanctions_value_minute_delay')) {
                $setting->sanctions_value_minute_delay = $request->sanctions_value_minute_delay;
            }

            $setting->save();

            return redirect()->route('generalsetting.index')
                ->with('success', 'تم تحديث البيانات بنجاح');

        } catch (\Exception $ex) {
            Log::error('Error during update: ' . $ex->getMessage());
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
}