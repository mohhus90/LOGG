<?php

namespace App\Http\Controllers\Admin\HR;

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
        $year     = (int)($request->year ?? now()->year);
        $comCode  = $this->comCode();
        $settings = Admin_panel_setting::where('com_code', $comCode)->first();

        // بحث متقدم
        $query = Employee::where('com_code', $comCode)
            ->with(['vacationBalance' => fn($q) => $q->where('year', $year)]);

        if ($request->filled('search_name'))
            $query->where('employee_name_A','like','%'.$request->search_name.'%');
        if ($request->filled('search_code'))
            $query->where('employee_id','like','%'.$request->search_code.'%');
        if ($request->filled('search_national'))
            $query->where('national_id','like','%'.$request->search_national.'%');
        if ($request->filled('has_balance')) {
            if ($request->has_balance == '1')
                $query->whereHas('vacationBalance', fn($q) => $q->where('year', $year));
            else
                $query->whereDoesntHave('vacationBalance', fn($q) => $q->where('year', $year));
        }

        $employees = $query->orderBy('employee_name_A')->paginate(25)->appends($request->except('page'));

        // إحصائيات
        $stats = EmployeeVacationBalance::where('com_code', $comCode)->where('year', $year)
            ->selectRaw('COUNT(*) as total_employees, SUM(annual_remaining) as total_annual_remaining,
                SUM(annual_used) as total_annual_used, SUM(casual_remaining) as total_casual_remaining')
            ->first();

        return view('admin.vacations.index',
            compact('employees', 'year', 'settings', 'stats'));
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
        $settings = Admin_panel_setting::where('com_code', $this->comCode())->first();
        return view('admin.vacations.edit', compact('employee', 'balance', 'year', 'settings'));
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

    public function deleteBalance(int $employeeId, int $year)
    {
        EmployeeVacationBalance::where('employee_id', $employeeId)
            ->where('year', $year)
            ->where('com_code', $this->comCode())
            ->delete();

        return redirect()->route('vacations.index', ['year' => $year])
            ->with('success', 'تم حذف رصيد الإجازة بنجاح');
    }

}
