<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KpiDefinition;
use App\Models\KpiEmployeeScore;
use App\Models\Employee;
use App\Exports\KpiTemplateExport;
use App\Imports\KpiScoresImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class KpiController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    // ─────────────────────────────────────────────
    // تعريف المؤشرات
    // ─────────────────────────────────────────────
    public function definitions()
    {
        // ✅ FIX: استخدام id بدلاً من sort_order لتجنب خطأ العمود المفقود
        // بعد تشغيل migration الإصلاح سيعود sort_order للعمل تلقائياً
        $orderColumn = Schema::hasColumn('kpi_definitions', 'sort_order') ? 'sort_order' : 'id';

        $kpis = KpiDefinition::where('com_code', $this->comCode())
            ->orderBy($orderColumn)->get();

        return view('admin.kpi.definitions', compact('kpis'));
    }

    public function createDefinition()
    {
        return view('admin.kpi.create_definition');
    }

    public function storeDefinition(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'code'           => 'required|string|max:50|unique:kpi_definitions,code',
            'category'       => 'required|string',
            'target_value'   => 'required|numeric|min:0',
            'weight'         => 'required|numeric|min:0|max:100',
            'affects_salary' => 'nullable|boolean',
        ], [
            'name.required'         => 'حقل اسم المؤشر مطلوب',
            'code.required'         => 'حقل كود المؤشر مطلوب',
            'code.unique'           => 'هذا الكود مستخدم من قبل',
            'category.required'     => 'اختر فئة المؤشر',
            'target_value.required' => 'أدخل القيمة المستهدفة',
            'weight.required'       => 'أدخل الوزن النسبي',
        ]);

        // ✅ FIX: استخراج البيانات يدوياً بدلاً من ...$request->except()
        // لمنع إرسال أعمدة غير موجودة في قاعدة البيانات
        $data = [
            'name'                => $request->name,
            'code'                => $request->code,
            'category'            => $request->category,
            'measurement_unit'    => $request->measurement_unit,
            'target_value'        => $request->target_value,
            'weight'              => $request->weight,
            'affects_salary'      => $request->boolean('affects_salary'),
            'salary_effect_type'  => $request->salary_effect_type   ?? 'bonus',
            'max_bonus_pct'       => $request->max_bonus_pct        ?? 0,
            'max_deduction_pct'   => $request->max_deduction_pct    ?? 0,
            'is_active'           => 1,
            'description'         => $request->description,
            'com_code'            => $this->comCode(),
            'added_by'            => Auth::guard('admin')->id(),
        ];

        // ✅ FIX: أضف sort_order فقط إذا كان العمود موجوداً في قاعدة البيانات
        if (Schema::hasColumn('kpi_definitions', 'sort_order')) {
            $data['sort_order'] = $request->sort_order ?? 0;
        }

        KpiDefinition::create($data);

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

        $data = [
            'name'               => $request->name,
            'code'               => $request->code,
            'category'           => $request->category,
            'measurement_unit'   => $request->measurement_unit,
            'target_value'       => $request->target_value,
            'weight'             => $request->weight,
            'affects_salary'     => $request->boolean('affects_salary'),
            'salary_effect_type' => $request->salary_effect_type ?? 'bonus',
            'max_bonus_pct'      => $request->max_bonus_pct      ?? 0,
            'max_deduction_pct'  => $request->max_deduction_pct  ?? 0,
            'is_active'          => $request->is_active           ?? 1,
            'description'        => $request->description,
        ];

        // ✅ FIX: sort_order اختياري
        if (Schema::hasColumn('kpi_definitions', 'sort_order')) {
            $data['sort_order'] = $request->sort_order ?? 0;
        }

        $kpi->update($data);

        return redirect()->route('kpi.definitions')->with('success', 'تم تحديث المؤشر بنجاح');
    }

    public function deleteDefinition(int $id)
    {
        KpiDefinition::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('kpi.definitions')->with('success', 'تم الحذف');
    }

    // ─────────────────────────────────────────────
    // إدخال قراءات الموظفين
    // ─────────────────────────────────────────────
    public function scores(Request $request)
    {
        $month     = $request->month ?? now()->month;
        $year      = $request->year  ?? now()->year;
        $employees = Employee::where('com_code', $this->comCode())
            ->orderBy('employee_name_A')->get();

        $orderColumn = Schema::hasColumn('kpi_definitions', 'sort_order') ? 'sort_order' : 'id';
        $kpis = KpiDefinition::where('com_code', $this->comCode())
            ->where('is_active', 1)->orderBy($orderColumn)->get();

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
                $employee = Employee::where('id', $empId)
                    ->where('com_code', $admin->com_code)->first();
                if (!$employee) continue;

                foreach ($kpiScores as $kpiId => $actualValue) {
                    if ($actualValue === null || $actualValue === '') continue;

                    $score = KpiEmployeeScore::firstOrNew([
                        'kpi_id'      => $kpiId,
                        'employee_id' => $empId,
                        'month'       => $month,
                        'year'        => $year,
                    ]);

                    $score->actual_value = (float)$actualValue;
                    $score->com_code     = (int)$admin->com_code;
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

    // ─────────────────────────────────────────────
    // دليل KPI (طباعة / PDF)
    // ─────────────────────────────────────────────
    public function guide()
    {
        return view('admin.kpi.guide');
    }

    // ─────────────────────────────────────────────
    // تصدير نموذج Excel للمديرين
    // ─────────────────────────────────────────────
    public function exportTemplate(Request $request)
    {
        $month   = (int) ($request->month ?? now()->month);
        $year    = (int) ($request->year  ?? now()->year);
        $admin   = Auth::guard('admin')->user();

        $monthName = \Carbon\Carbon::create($year, $month, 1)->locale('ar')->monthName;
        $filename  = "kpi_template_{$year}_{$month}.xlsx";

        return Excel::download(
            new KpiTemplateExport($month, $year, (int)$admin->com_code, $admin->id),
            $filename
        );
    }

    // ─────────────────────────────────────────────
    // استيراد ملف Excel المملوء من المديرين
    // ─────────────────────────────────────────────
    public function importScores(Request $request)
    {
        $request->validate([
            'kpi_file' => 'required|file|mimes:xlsx,xls',
            'month'    => 'required|integer|between:1,12',
            'year'     => 'required|integer|min:2020',
        ], [
            'kpi_file.required' => 'يرجى اختيار ملف Excel',
            'kpi_file.mimes'    => 'الملف يجب أن يكون بصيغة xlsx أو xls',
        ]);

        $admin  = Auth::guard('admin')->user();
        $month  = (int) $request->month;
        $year   = (int) $request->year;

        $import = new KpiScoresImport($month, $year, (int)$admin->com_code, $admin->id);
        Excel::import($import, $request->file('kpi_file'));

        if (!empty($import->errors)) {
            return back()->with('warning',
                "تم الاستيراد جزئياً — تم: {$import->imported} | تجاهل: {$import->skipped}<br>" .
                implode('<br>', $import->errors)
            );
        }

        return redirect()->route('kpi.scores', ['month' => $month, 'year' => $year])
            ->with('success', "✅ تم استيراد {$import->imported} قراءة من ملف Excel بنجاح.");
    }

    // ─────────────────────────────────────────────
    // تقرير KPI الشهري
    // ─────────────────────────────────────────────
    public function report(Request $request)
    {
        $month      = $request->month      ?? now()->month;
        $year       = $request->year       ?? now()->year;
        $employeeId = $request->employee_id ?? null;
        $kpiId      = $request->kpi_id      ?? null;
        $category   = $request->category    ?? null;
        $sort       = in_array($request->sort, ['score','achievement','bonus','name']) ? $request->sort : 'score';
        $dir        = $request->dir === 'asc' ? 'asc' : 'desc';

        $orderColumn = Schema::hasColumn('kpi_definitions', 'sort_order') ? 'sort_order' : 'id';
        $employees = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        $kpiDefs   = KpiDefinition::where('com_code', $this->comCode())->where('is_active', 1)->orderBy($orderColumn)->get();

        $query = KpiEmployeeScore::with(['employee', 'kpi'])
            ->where('com_code', $this->comCode())
            ->where('month', $month)->where('year', $year);

        if ($employeeId) $query->where('employee_id', $employeeId);
        if ($kpiId)      $query->where('kpi_id', $kpiId);
        if ($category) {
            $catKpiIds = KpiDefinition::where('com_code', $this->comCode())
                ->where('category', $category)->pluck('id');
            $query->whereIn('kpi_id', $catKpiIds);
        }

        $scores = $query->get();

        $byEmployee = $scores->groupBy('employee_id')->map(function ($empScores) {
            $emp = $empScores->first()->employee;
            return [
                'employee'        => $emp,
                'total_score'     => round($empScores->sum('score'), 2),
                'avg_achievement' => round($empScores->avg('achievement_pct'), 1),
                'total_bonus'     => $empScores->where('effect_direction', 1)->sum('salary_effect_amount'),
                'total_deduction' => $empScores->where('effect_direction', 2)->sum('salary_effect_amount'),
                'net_effect'      => $empScores->where('effect_direction', 1)->sum('salary_effect_amount')
                                   - $empScores->where('effect_direction', 2)->sum('salary_effect_amount'),
                'scores'          => $empScores,
            ];
        });

        $byEmployee = match ($sort) {
            'achievement' => $dir === 'desc' ? $byEmployee->sortByDesc('avg_achievement') : $byEmployee->sortBy('avg_achievement'),
            'bonus'       => $dir === 'desc' ? $byEmployee->sortByDesc('net_effect')      : $byEmployee->sortBy('net_effect'),
            'name'        => $dir === 'desc'
                ? $byEmployee->sortByDesc(fn($d) => $d['employee']->employee_name_A ?? '')
                : $byEmployee->sortBy(fn($d) => $d['employee']->employee_name_A ?? ''),
            default       => $dir === 'desc' ? $byEmployee->sortByDesc('total_score')     : $byEmployee->sortBy('total_score'),
        };

        return view('admin.kpi.report', compact(
            'byEmployee', 'month', 'year',
            'employees', 'kpiDefs',
            'sort', 'dir', 'employeeId', 'kpiId', 'category'
        ));
    }
}
