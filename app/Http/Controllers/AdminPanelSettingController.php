<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;

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

            $logoPath = $setting->image ?? $setting->logo ?? null;
            if ($request->hasFile('logo_file') && $request->file('logo_file')->isValid()) {
                if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                    Storage::disk('public')->delete($logoPath);
                }
                $logoPath = $request->file('logo_file')->store('logos', 'public');
            }

            $updatedData = [
                'updated_by'                     => Auth::guard('admin')->id(),
                'com_name'                       => $request->com_name            ?? $setting->com_name,
                'com_code'                       => $this->getComCode(),
                'saysem_status'                  => $request->saysem_status       ?? 1,
                'phone'                          => $request->phone               ?? '',
                'email'                          => $request->email               ?? '',
                'address'                        => $request->address             ?? '',
                'image'                          => $logoPath,
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
                // أوفرتايم ثابت
                'overtime_calc_type'                    => (int)($request->overtime_calc_type ?? 1),
                'max_monthly_overtime_hours'            => (float)($request->max_monthly_overtime_hours ?? 0),
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
}
