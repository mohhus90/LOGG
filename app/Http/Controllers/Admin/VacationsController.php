<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeVacationBalance;
use App\Models\Admin_panel_setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VacationsController extends Controller
{
    private function comCode(): int
    {
        return Auth::guard('admin')->user()->com_code;
    }

    // =========================================================
    //  عرض أرصدة الإجازات لجميع الموظفين
    // =========================================================
    public function index(Request $request)
    {
        $year      = $request->year ?? now()->year;
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();

        // جلب الأرصدة مع الموظفين الذين ليس لهم رصيد بعد
        $balances = EmployeeVacationBalance::where('com_code', $this->comCode())
            ->where('year', $year)
            ->get()->keyBy('employee_id');

        return view('admin.vacations.index', compact('employees', 'balances', 'year'));
    }

    // =========================================================
    //  إنشاء رصيد سنوي لجميع الموظفين دفعة واحدة
    // =========================================================
    public function createBulk(Request $request)
    {
        $request->validate(['year' => 'required|integer|min:2020']);

        $year     = $request->year;
        $settings = Admin_panel_setting::where('com_code', $this->comCode())->first();
        $employees = Employee::where('com_code', $this->comCode())->get();
        $created   = 0;
        $skipped   = 0;

        DB::beginTransaction();
        try {
            foreach ($employees as $emp) {
                $exists = EmployeeVacationBalance::where('employee_id', $emp->id)
                    ->where('year', $year)->exists();

                if ($exists) { $skipped++; continue; }

                EmployeeVacationBalance::createForEmployee($emp, $year, $settings);
                $created++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        return back()->with('success',
            "تم إنشاء رصيد سنة $year لـ $created موظف." .
            ($skipped ? " ($skipped موظف لديهم رصيد مسبقاً)" : '')
        );
    }

    // =========================================================
    //  تعديل رصيد موظف معين
    // =========================================================
    public function edit(int $employeeId, int $year)
    {
        $employee = Employee::where('com_code', $this->comCode())->findOrFail($employeeId);
        $balance  = EmployeeVacationBalance::firstOrNew(
            ['employee_id' => $employeeId, 'year' => $year],
            ['com_code' => $this->comCode()]
        );
        return view('admin.vacations.edit', compact('employee', 'balance', 'year'));
    }

    public function update(Request $request, int $employeeId, int $year)
    {
        $request->validate([
            'annual_balance'   => 'required|numeric|min:0',
            'annual_remaining' => 'required|numeric|min:0',
            'casual_balance'   => 'required|numeric|min:0',
            'casual_remaining' => 'required|numeric|min:0',
        ]);

        EmployeeVacationBalance::updateOrCreate(
            ['employee_id' => $employeeId, 'year' => $year],
            [
                'annual_balance'    => $request->annual_balance,
                'annual_used'       => $request->annual_balance - $request->annual_remaining,
                'annual_remaining'  => $request->annual_remaining,
                'casual_balance'    => $request->casual_balance,
                'casual_used'       => $request->casual_balance - $request->casual_remaining,
                'casual_remaining'  => $request->casual_remaining,
                'monthly_accrual'   => $request->monthly_accrual ?? 1.75,
                'com_code'          => $this->comCode(),
            ]
        );

        return redirect()->route('vacations.index', ['year' => $year])
            ->with('success', 'تم تحديث رصيد الإجازات بنجاح');
    }

    // =========================================================
    //  تشغيل الاستحقاق الشهري يدوياً (أو يُستدعى من Scheduler)
    // =========================================================
    public function runMonthlyAccrual(Request $request)
    {
        $year   = $request->year  ?? now()->year;
        $count  = 0;

        $balances = EmployeeVacationBalance::where('com_code', $this->comCode())
            ->where('year', $year)->get();

        foreach ($balances as $balance) {
            $balance->addMonthlyAccrual();
            $count++;
        }

        return back()->with('success',
            "تم إضافة الاستحقاق الشهري لـ $count موظف في سنة $year"
        );
    }

    // =========================================================
    //  Artisan Command / Scheduler يستدعي هذا
    //  php artisan schedule:run (يُعرَّف في Console/Kernel.php)
    // =========================================================
    public static function scheduledMonthlyAccrual(): void
    {
        // يُستدعى تلقائياً أول كل شهر
        $balances = EmployeeVacationBalance::where('year', now()->year)->get();
        foreach ($balances as $balance) {
            $balance->addMonthlyAccrual();
        }
    }
}
