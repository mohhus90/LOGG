<?php
// FILE: app/Http/Controllers/Admin/KpiController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KpiDefinition;
use App\Models\KpiEmployeeScore;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    private function comCode(): int { return Auth::guard('admin')->user()->com_code; }

    // ── تعريف المؤشرات ──
    public function definitions()
    {
        $kpis = KpiDefinition::where('com_code', $this->comCode())->orderBy('sort_order')->get();
        return view('admin.kpi.definitions', compact('kpis'));
    }

    public function createDefinition()
    {
        return view('admin.kpi.create_definition');
    }

    public function storeDefinition(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'code'             => 'required|string|max:50|unique:kpi_definitions,code',
            'category'         => 'required|string',
            'target_value'     => 'required|numeric|min:0',
            'weight'           => 'required|numeric|min:0|max:100',
            'affects_salary'   => 'nullable|boolean',
        ]);

        KpiDefinition::create([
            ...$request->except('_token'),
            'affects_salary'   => $request->boolean('affects_salary'),
            'is_active'        => 1,
            'com_code'         => $this->comCode(),
            'added_by'         => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('kpi.definitions')
            ->with('success', 'تم إضافة مؤشر الأداء بنجاح');
    }

    public function editDefinition(int $id)
    {
        $kpi = KpiDefinition::where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.kpi.create_definition', compact('kpi'));
    }

    public function updateDefinition(Request $request, int $id)
    {
        $kpi = KpiDefinition::where('com_code', $this->comCode())->findOrFail($id);
        $kpi->update([...$request->except('_token','_method'), 'affects_salary' => $request->boolean('affects_salary')]);
        return redirect()->route('kpi.definitions')->with('success', 'تم تحديث المؤشر');
    }

    public function deleteDefinition(int $id)
    {
        KpiDefinition::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('kpi.definitions')->with('success', 'تم الحذف');
    }

    // ── إدخال قراءات الموظفين ──
    public function scores(Request $request)
    {
        $month     = $request->month ?? now()->month;
        $year      = $request->year  ?? now()->year;
        $employees = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        $kpis      = KpiDefinition::where('com_code', $this->comCode())->where('is_active', 1)->get();

        // جلب النتائج الحالية
        $scores = KpiEmployeeScore::where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get()->groupBy('employee_id');

        return view('admin.kpi.scores', compact('employees', 'kpis', 'scores', 'month', 'year'));
    }

    public function saveScores(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;
        $admin = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            foreach ($request->scores ?? [] as $empId => $kpiScores) {
                $employee = Employee::where('id', $empId)->where('com_code', $admin->com_code)->first();
                if (!$employee) continue;

                foreach ($kpiScores as $kpiId => $actualValue) {
                    if ($actualValue === null || $actualValue === '') continue;

                    $kpi   = KpiDefinition::find($kpiId);
                    $score = KpiEmployeeScore::firstOrNew([
                        'kpi_id'      => $kpiId,
                        'employee_id' => $empId,
                        'month'       => $month,
                        'year'        => $year,
                    ]);

                    $score->actual_value = (float) $actualValue;
                    $score->com_code     = $admin->com_code;
                    $score->added_by     = $admin->id;
                    $score->calculate($employee->emp_sal ?? 0);
                    $score->save();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        return redirect()->route('kpi.scores', ['month' => $month, 'year' => $year])
            ->with('success', "تم حفظ تقييمات {$month}/{$year}");
    }

    // ── تقرير KPI الشهري ──
    public function report(Request $request)
    {
        $month     = $request->month ?? now()->month;
        $year      = $request->year  ?? now()->year;

        $scores = KpiEmployeeScore::with(['employee', 'kpi'])
            ->where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year)
            ->get();

        // تجميع بالموظف
        $byEmployee = $scores->groupBy('employee_id')->map(function ($empScores) {
            $emp = $empScores->first()->employee;
            return [
                'employee'         => $emp,
                'total_score'      => $empScores->sum('score'),
                'avg_achievement'  => $empScores->avg('achievement_pct'),
                'total_bonus'      => $empScores->where('effect_direction', 1)->sum('salary_effect_amount'),
                'total_deduction'  => $empScores->where('effect_direction', 2)->sum('salary_effect_amount'),
                'net_effect'       => $empScores->where('effect_direction', 1)->sum('salary_effect_amount')
                                    - $empScores->where('effect_direction', 2)->sum('salary_effect_amount'),
                'scores'           => $empScores,
            ];
        })->sortByDesc('total_score');

        return view('admin.kpi.report', compact('byEmployee', 'month', 'year'));
    }
}
