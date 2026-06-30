<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\KpiDefinition;
use App\Models\KpiEmployeeScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class KpiScoresImport implements ToCollection, WithCalculatedFormulas
{
    private int $month;
    private int $year;
    private int $comCode;
    private int $addedBy;

    // نتائج الاستيراد
    public int $imported  = 0;
    public int $skipped   = 0;
    public array $errors  = [];

    public function __construct(int $month, int $year, int $comCode, int $addedBy)
    {
        $this->month   = $month;
        $this->year    = $year;
        $this->comCode = $comCode;
        $this->addedBy = $addedBy;
    }

    public function collection(Collection $rows): void
    {
        // ── الصف 4 (index 4): أكواد الاستيراد (KPI_[id], PCT_..., EFF_...)
        // ── الصفوف من index 6 فصاعداً: بيانات الموظفين
        // (الفهارس: 0=ROW1, 1=ROW2, 2=ROW3, 3=ROW4, 4=ROW5_map, 5=ROW6_display, 6+=data)

        if ($rows->count() < 5) {
            $this->errors[] = 'الملف فارغ أو لا يحتوي على الهيكل الصحيح.';
            return;
        }

        // ── قراءة خريطة الأعمدة من الصف 5 (index 4) ──
        $mapRow = $rows->get(4);
        if (!$mapRow) {
            $this->errors[] = 'لم يُعثر على صف خريطة المؤشرات (الصف 5).';
            return;
        }

        // map: colIndex => kpiId
        $kpiColMap = [];
        foreach ($mapRow as $colIndex => $header) {
            if (str_starts_with((string)$header, 'KPI_')) {
                $kpiId = (int) substr($header, 4);
                if ($kpiId > 0) {
                    $kpiColMap[$colIndex] = $kpiId;
                }
            }
        }

        if (empty($kpiColMap)) {
            $this->errors[] = 'لم يُعثر على أعمدة KPI في الملف. تأكد من استخدام النموذج الصحيح.';
            return;
        }

        // تحقق من أن المؤشرات تعود لنفس الشركة
        $validKpiIds = KpiDefinition::where('com_code', $this->comCode)
            ->pluck('id')->toArray();

        $kpiColMap = array_filter($kpiColMap, fn($id) => in_array($id, $validKpiIds));

        if (empty($kpiColMap)) {
            $this->errors[] = 'لا تنتمي المؤشرات في الملف لهذه الشركة.';
            return;
        }

        // preload KPI objects
        $kpiObjects = KpiDefinition::whereIn('id', array_values($kpiColMap))
            ->get()->keyBy('id');

        // preload employees
        $employees = Employee::where('com_code', $this->comCode)
            ->get()->keyBy('id');

        DB::beginTransaction();
        try {
            // البيانات تبدأ من index 6 (الصف 7)
            $dataRows = $rows->slice(6);

            foreach ($dataRows as $rowIndex => $row) {
                $empId = isset($row[0]) ? (int) $row[0] : 0;
                if (!$empId) continue;

                $employee = $employees->get($empId);
                if (!$employee) {
                    $this->errors[] = "موظف غير معروف: ID={$empId} (صف " . ($rowIndex + 7) . ")";
                    $this->skipped++;
                    continue;
                }

                foreach ($kpiColMap as $colIndex => $kpiId) {
                    $actualValue = isset($row[$colIndex]) ? $row[$colIndex] : null;

                    if ($actualValue === null || $actualValue === '') {
                        $this->skipped++;
                        continue;
                    }

                    $actualValue = (float) $actualValue;
                    $kpi = $kpiObjects->get($kpiId);

                    if (!$kpi) {
                        $this->skipped++;
                        continue;
                    }

                    $score = KpiEmployeeScore::firstOrNew([
                        'kpi_id'      => $kpiId,
                        'employee_id' => $empId,
                        'month'       => $this->month,
                        'year'        => $this->year,
                    ]);

                    $score->actual_value = $actualValue;
                    $score->com_code     = $this->comCode;
                    $score->added_by     = $this->addedBy;
                    $score->setRelation('kpi', $kpi); // تجنب query إضافي
                    $score->calculate($employee->emp_sal ?? 0);
                    $score->save();

                    $this->imported++;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = 'خطأ أثناء الحفظ: ' . $e->getMessage();
        }
    }
}
