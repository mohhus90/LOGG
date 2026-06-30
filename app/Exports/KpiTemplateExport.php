<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\KpiDefinition;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class KpiTemplateExport implements WithEvents, WithTitle
{
    // صفوف رأس الجدول (البيانات تبدأ من DATA_START_ROW)
    const META_ROW        = 2;  // صف البيانات الوصفية (للاستيراد)
    const KPI_MAP_ROW     = 5;  // صف أكواد KPI (للاستيراد)
    const DISPLAY_HDR_ROW = 6;  // صف العناوين المرئية
    const DATA_START_ROW  = 7;  // أول صف بيانات

    private int    $month;
    private int    $year;
    private int    $comCode;
    private array  $kpis;
    private array  $employees;
    private int    $addedBy;

    public function __construct(int $month, int $year, int $comCode, int $addedBy)
    {
        $this->month     = $month;
        $this->year      = $year;
        $this->comCode   = $comCode;
        $this->addedBy   = $addedBy;

        $orderColumn    = \Illuminate\Support\Facades\Schema::hasColumn('kpi_definitions', 'sort_order')
            ? 'sort_order' : 'id';

        $this->kpis      = KpiDefinition::where('com_code', $comCode)
            ->where('is_active', 1)
            ->orderBy($orderColumn)
            ->get()
            ->toArray();

        $this->employees = Employee::where('com_code', $comCode)
            ->orderBy('employee_name_A')
            ->get(['id', 'employee_name_A', 'emp_sal'])
            ->toArray();
    }

    public function title(): string
    {
        return 'تقييم KPI';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet  = $event->sheet->getDelegate();
                $kpis   = $this->kpis;
                $emps   = $this->employees;
                $nKpi   = count($kpis);
                $nEmp   = count($emps);

                // ── أعمدة الهيكل ──
                // A=1 emp_id | B=2 emp_name | C=3 salary (مخفي)
                // ثم لكل KPI: 3 أعمدة (actual, achievement%, effect)
                // ثم 3 أعمدة نهائية: total_bonus, total_deduction, net_effect
                $FIXED      = 3;
                $KPI_COLS   = 3; // actual + % + effect per KPI
                $SUMMARY    = 3;
                $lastCol    = $FIXED + $nKpi * $KPI_COLS + $SUMMARY;
                $lastColLtr = Coordinate::stringFromColumnIndex($lastCol);

                // ── RTL ──
                $sheet->setRightToLeft(true);

                // ─────────────────────────────────────────────
                // ROW 1 — عنوان رئيسي
                // ─────────────────────────────────────────────
                $monthName = \Carbon\Carbon::create($this->year, $this->month, 1)
                    ->locale('ar')->monthName;
                $sheet->mergeCells("A1:{$lastColLtr}1");
                $sheet->setCellValue('A1',
                    "🏆 شيت تقييم مؤشرات الأداء (KPIs) — {$monthName} {$this->year}");
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '1A365D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(36);

                // ─────────────────────────────────────────────
                // ROW 2 — بيانات وصفية للاستيراد (مخفي)
                // ─────────────────────────────────────────────
                $sheet->setCellValue('A2',
                    "META|MONTH:{$this->month}|YEAR:{$this->year}|COMCODE:{$this->comCode}|ADDEDBY:{$this->addedBy}");
                $sheet->getStyle('A2')->getFont()->getColor()->setRGB('EEEEEE');
                $sheet->getStyle('A2')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
                $sheet->mergeCells("B2:{$lastColLtr}2");
                $sheet->getRowDimension(2)->setRowHeight(14);

                // ─────────────────────────────────────────────
                // ROW 3 — تعليمات
                // ─────────────────────────────────────────────
                $sheet->mergeCells("A3:{$lastColLtr}3");
                $sheet->setCellValue('A3',
                    '⚠️  تعليمات: يُرجى إدخال القيم الفعلية فقط في الخلايا ذات الخلفية الصفراء. الخلايا الرمادية تُحسب تلقائياً. لا تعدّل أعمدة "كود الموظف" أو "الراتب".');
                $sheet->getStyle('A3')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '744210']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEFCBF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT,
                                    'wrapText'   => true],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(28);

                // ─────────────────────────────────────────────
                // ROW 4 — تفاصيل المؤشرات (للمرجع)
                // ─────────────────────────────────────────────
                $sheet->mergeCells("A4:{$lastColLtr}4");
                $kpiSummary = collect($kpis)->map(fn($k) =>
                    "{$k['name']} (هدف: {$k['target_value']} {$k['measurement_unit']} | وزن: {$k['weight']}%)"
                )->implode(' | ');
                $sheet->setCellValue('A4', "📋 المؤشرات: {$kpiSummary}");
                $sheet->getStyle('A4')->applyFromArray([
                    'font'      => ['size' => 10, 'color' => ['rgb' => '2D3748']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EBF8FF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(4)->setRowHeight(22);

                // ─────────────────────────────────────────────
                // ROW 5 — أكواد الاستيراد (مخفية بالنص الفاتح)
                // ─────────────────────────────────────────────
                $sheet->setCellValue('A' . self::KPI_MAP_ROW, 'EMP_ID');
                $sheet->setCellValue('B' . self::KPI_MAP_ROW, 'EMP_NAME');
                $sheet->setCellValue('C' . self::KPI_MAP_ROW, 'EMP_SAL');

                foreach ($kpis as $i => $kpi) {
                    $baseCol    = $FIXED + $i * $KPI_COLS + 1;
                    $actualLtr  = Coordinate::stringFromColumnIndex($baseCol);
                    $pctLtr     = Coordinate::stringFromColumnIndex($baseCol + 1);
                    $effLtr     = Coordinate::stringFromColumnIndex($baseCol + 2);
                    $sheet->setCellValue("{$actualLtr}" . self::KPI_MAP_ROW, "KPI_{$kpi['id']}");
                    $sheet->setCellValue("{$pctLtr}"    . self::KPI_MAP_ROW, "PCT_{$kpi['id']}");
                    $sheet->setCellValue("{$effLtr}"    . self::KPI_MAP_ROW, "EFF_{$kpi['id']}");
                }

                $sumBase   = $FIXED + $nKpi * $KPI_COLS + 1;
                $bonusLtr  = Coordinate::stringFromColumnIndex($sumBase);
                $deductLtr = Coordinate::stringFromColumnIndex($sumBase + 1);
                $netLtr    = Coordinate::stringFromColumnIndex($sumBase + 2);
                $sheet->setCellValue("{$bonusLtr}" . self::KPI_MAP_ROW,  'TOTAL_BONUS');
                $sheet->setCellValue("{$deductLtr}" . self::KPI_MAP_ROW, 'TOTAL_DEDUCTION');
                $sheet->setCellValue("{$netLtr}" . self::KPI_MAP_ROW,    'NET_EFFECT');

                // style row 5 (machine-readable, nearly invisible)
                $sheet->getStyle("A5:{$lastColLtr}5")->applyFromArray([
                    'font' => ['size' => 8, 'color' => ['rgb' => 'CCCCCC']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F7FAFC']],
                ]);
                $sheet->getRowDimension(5)->setRowHeight(12);

                // ─────────────────────────────────────────────
                // ROW 6 — عناوين مرئية للمدير
                // ─────────────────────────────────────────────
                $sheet->setCellValue('A' . self::DISPLAY_HDR_ROW, 'كود الموظف');
                $sheet->setCellValue('B' . self::DISPLAY_HDR_ROW, 'اسم الموظف');
                $sheet->setCellValue('C' . self::DISPLAY_HDR_ROW, 'الراتب الأساسي');

                foreach ($kpis as $i => $kpi) {
                    $baseCol    = $FIXED + $i * $KPI_COLS + 1;
                    $actualLtr  = Coordinate::stringFromColumnIndex($baseCol);
                    $pctLtr     = Coordinate::stringFromColumnIndex($baseCol + 1);
                    $effLtr     = Coordinate::stringFromColumnIndex($baseCol + 2);

                    $effectLabel = match ($kpi['salary_effect_type']) {
                        'bonus'     => 'مكافأة ج.م',
                        'deduction' => 'خصم ج.م',
                        default     => 'تأثير ج.م',
                    };
                    $affectsMark = $kpi['affects_salary'] ? '' : ' (إحصاء)';

                    $sheet->setCellValue("{$actualLtr}" . self::DISPLAY_HDR_ROW,
                        "{$kpi['name']}{$affectsMark}\n(هدف: {$kpi['target_value']} {$kpi['measurement_unit']})");
                    $sheet->setCellValue("{$pctLtr}" . self::DISPLAY_HDR_ROW, 'نسبة الإنجاز %');
                    $sheet->setCellValue("{$effLtr}" . self::DISPLAY_HDR_ROW, $effectLabel);
                }

                $sheet->setCellValue("{$bonusLtr}"  . self::DISPLAY_HDR_ROW, 'إجمالي المكافآت ج.م');
                $sheet->setCellValue("{$deductLtr}" . self::DISPLAY_HDR_ROW, 'إجمالي الخصومات ج.م');
                $sheet->setCellValue("{$netLtr}"    . self::DISPLAY_HDR_ROW, 'الصافي ج.م');

                $sheet->getStyle("A6:{$lastColLtr}6")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '2B6CB0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical'   => Alignment::VERTICAL_CENTER,
                                    'wrapText'   => true],
                ]);
                $sheet->getRowDimension(6)->setRowHeight(40);

                // ─────────────────────────────────────────────
                // DATA ROWS
                // ─────────────────────────────────────────────
                foreach ($emps as $eIdx => $emp) {
                    $row   = self::DATA_START_ROW + $eIdx;
                    $cSal  = 'C' . $row;

                    $sheet->setCellValue('A' . $row, $emp['id']);
                    $sheet->setCellValue('B' . $row, $emp['employee_name_A']);
                    $sheet->setCellValue($cSal, $emp['emp_sal'] ?? 0);

                    // collect effect column letters for summary formulas
                    $effCols = [];

                    foreach ($kpis as $i => $kpi) {
                        $baseCol   = $FIXED + $i * $KPI_COLS + 1;
                        $actualLtr = Coordinate::stringFromColumnIndex($baseCol);
                        $pctLtr    = Coordinate::stringFromColumnIndex($baseCol + 1);
                        $effLtr    = Coordinate::stringFromColumnIndex($baseCol + 2);
                        $cellAct   = "{$actualLtr}{$row}";
                        $cellPct   = "{$pctLtr}{$row}";
                        $cellEff   = "{$effLtr}{$row}";

                        $target   = (float) $kpi['target_value'] ?: 1;
                        $maxBonus = (float) $kpi['max_bonus_pct'];
                        $maxDed   = (float) $kpi['max_deduction_pct'];
                        $type     = $kpi['salary_effect_type'];
                        $affects  = (bool) $kpi['affects_salary'];

                        // Achievement %
                        $sheet->setCellValue($cellPct,
                            "=IF({$cellAct}=\"\",\"\",ROUND({$cellAct}/{$target}*100,2))");

                        // Salary effect formula
                        if (!$affects || ($maxBonus == 0 && $maxDed == 0)) {
                            $sheet->setCellValue($cellEff, "=IF({$cellAct}=\"\",\"\",0)");
                        } else {
                            $bonusPart  = ($type === 'bonus' || $type === 'both') && $maxBonus > 0
                                ? "IF({$cellAct}/{$target}>=1,MIN(({$cellAct}/{$target}-1)*{$maxBonus},{$maxBonus})*{$cSal}/100,0)"
                                : "0";
                            $deductPart = ($type === 'deduction' || $type === 'both') && $maxDed > 0
                                ? "IF({$cellAct}/{$target}<1,-MIN((1-{$cellAct}/{$target})*{$maxDed},{$maxDed})*{$cSal}/100,0)"
                                : "0";
                            $sheet->setCellValue($cellEff,
                                "=IF({$cellAct}=\"\",\"\",{$bonusPart}+{$deductPart})");
                        }

                        $effCols[] = $cellEff;

                        // Style actual cell (yellow)
                        $sheet->getStyle($cellAct)->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FEFCBF']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                        // Style formula cells (light gray)
                        $sheet->getStyle($cellPct . ':' . $cellEff)->applyFromArray([
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'F0F4F8']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                        // Conditional: color achievement cell - we can't do true conditional from PHP easily,
                        // but we can add a note or rely on the user's view
                    }

                    // Summary cells
                    if (!empty($effCols)) {
                        $effRange = implode(',', $effCols);
                        $sheet->setCellValue("{$bonusLtr}{$row}",
                            "=SUMIF({$effRange},\">0\")");
                        $sheet->setCellValue("{$deductLtr}{$row}",
                            "=-SUMIF({$effRange},\"<0\")");
                        $sheet->setCellValue("{$netLtr}{$row}",
                            "=SUM({$effRange})");
                    }

                    // Style entire row
                    $bgColor = $eIdx % 2 === 0 ? 'FFFFFF' : 'F7FAFC';
                    $sheet->getStyle("A{$row}:{$lastColLtr}{$row}")->applyFromArray([
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    // Override yellow on actual cols and gray on formula cols (already set above)
                    // Style ID/Name/Salary cells (gray, locked-looking)
                    $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EDF2F7']],
                    ]);
                    // Style summary cols
                    $sheet->getStyle("{$bonusLtr}{$row}:{$netLtr}{$row}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EBF8FF']],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(20);
                }

                // ─────────────────────────────────────────────
                // BORDERS on the full table
                // ─────────────────────────────────────────────
                if ($nEmp > 0) {
                    $lastDataRow = self::DATA_START_ROW + $nEmp - 1;
                    $sheet->getStyle("A6:{$lastColLtr}{$lastDataRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'CBD5E0'],
                            ],
                        ],
                    ]);
                    // Thicker outer border
                    $sheet->getStyle("A6:{$lastColLtr}{$lastDataRow}")->applyFromArray([
                        'borders' => [
                            'outline' => [
                                'borderStyle' => Border::BORDER_MEDIUM,
                                'color'       => ['rgb' => '2B6CB0'],
                            ],
                        ],
                    ]);
                }

                // ─────────────────────────────────────────────
                // COLUMN WIDTHS
                // ─────────────────────────────────────────────
                $sheet->getColumnDimension('A')->setWidth(10);  // emp_id (narrow, gray)
                $sheet->getColumnDimension('B')->setWidth(28);  // name
                $sheet->getColumnDimension('C')->setVisible(false); // salary hidden

                foreach ($kpis as $i => $kpi) {
                    $baseCol   = $FIXED + $i * $KPI_COLS + 1;
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($baseCol))->setWidth(18);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($baseCol + 1))->setWidth(14);
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($baseCol + 2))->setWidth(14);
                }
                $sheet->getColumnDimension($bonusLtr)->setWidth(18);
                $sheet->getColumnDimension($deductLtr)->setWidth(18);
                $sheet->getColumnDimension($netLtr)->setWidth(14);

                // Freeze panes: rows 1-6 + col B
                $sheet->freezePane('C' . self::DATA_START_ROW);

                // ─────────────────────────────────────────────
                // INSTRUCTIONS SHEET
                // ─────────────────────────────────────────────
                $wb         = $sheet->getParent();
                $instrSheet = $wb->createSheet();
                $instrSheet->setTitle('تعليمات');
                $instrSheet->setRightToLeft(true);

                $instrSheet->setCellValue('A1', '📋 تعليمات ملء شيت تقييم الأداء (KPIs)');
                $instrSheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1A365D']],
                ]);

                $lines = [
                    'A3'  => '1.  افتح ورقة "تقييم KPI" — وهي الورقة الرئيسية للإدخال.',
                    'A4'  => '2.  ادخل القيم الفعلية فقط في الخلايا ذات الخلفية الصفراء.',
                    'A5'  => '3.  الخلايا الرمادية (نسبة الإنجاز والتأثير المالي) تُحسب تلقائياً — لا تعدّلها.',
                    'A6'  => '4.  لا تحذف أو تعدّل الصفوف 1 إلى 6 (صفوف الرأس).',
                    'A7'  => '5.  لا تعدّل العمود A (كود الموظف) أو العمود C (الراتب) — يستخدمها البرنامج للاستيراد.',
                    'A8'  => '6.  احفظ الملف بصيغة Excel (.xlsx) ثم استورده من البرنامج.',
                    'A9'  => '7.  في حالة اقتراح القيمة الفعلية بالصفر، أدخل 0 صراحةً ولا تتركها فارغة.',
                    'A11' => '🔑  أكواد الألوان:',
                    'A12' => '    🟡 خلفية صفراء  =  قيمة فعلية (أنت تملأها)',
                    'A13' => '    ⬜ خلفية رمادية  =  حساب تلقائي (لا تعدّله)',
                    'A14' => '    🔵 رأس الجدول الأزرق  =  أسماء المؤشرات والأهداف',
                    'A16' => '📊  المؤشرات المضمّنة في هذا الشيت:',
                ];

                foreach ($lines as $cell => $text) {
                    $instrSheet->setCellValue($cell, $text);
                }

                $rowI = 17;
                foreach ($kpis as $k) {
                    $affLbl  = $k['affects_salary'] ? "يؤثر على الراتب ({$k['salary_effect_type']})" : 'إحصاء فقط';
                    $instrSheet->setCellValue("A{$rowI}",
                        "   • {$k['name']} — هدف: {$k['target_value']} {$k['measurement_unit']} | وزن: {$k['weight']}% | {$affLbl}");
                    $rowI++;
                }

                $instrSheet->getColumnDimension('A')->setWidth(80);
                foreach (range(1, $rowI) as $r) {
                    $instrSheet->getRowDimension($r)->setRowHeight(18);
                }
                $instrSheet->getStyle("A1:A{$rowI}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}
