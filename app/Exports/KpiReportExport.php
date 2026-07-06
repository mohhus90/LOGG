<?php

namespace App\Exports;

use App\Models\KpiEmployeeScore;
use App\Models\KpiDefinition;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class KpiReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $filters;
    protected int   $comCode;

    private static array $categoryLabels = [
        'performance' => 'أداء',    'quality'    => 'جودة',
        'attendance'  => 'حضور',    'sales'      => 'مبيعات',
        'custom'      => 'مخصص',
    ];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->comCode = (int) Auth::guard('admin')->user()->com_code;
    }

    public function query()
    {
        $q = KpiEmployeeScore::with(['employee', 'kpi'])
            ->where('com_code', $this->comCode);

        if (!empty($this->filters['month']))       $q->where('month', $this->filters['month']);
        if (!empty($this->filters['year']))        $q->where('year',  $this->filters['year']);
        if (!empty($this->filters['employee_id'])) $q->where('employee_id', $this->filters['employee_id']);
        if (!empty($this->filters['kpi_id']))      $q->where('kpi_id', $this->filters['kpi_id']);
        if (!empty($this->filters['category'])) {
            $catIds = KpiDefinition::where('com_code', $this->comCode)
                ->where('category', $this->filters['category'])->pluck('id');
            $q->whereIn('kpi_id', $catIds);
        }

        return $q->orderBy('employee_id')->orderBy('kpi_id');
    }

    public function headings(): array
    {
        return [
            'م', 'اسم الموظف', 'رقم الموظف', 'المؤشر', 'الفئة',
            'وحدة القياس', 'الهدف', 'الفعلي', 'التحقق %', 'الوزن',
            'النقاط', 'التأثير المالي (ج.م)', 'الاتجاه',
        ];
    }

    public function map($row): array
    {
        static $i = 0; $i++;
        $direction = match ((string)$row->effect_direction) {
            '1' => 'مكافأة',
            '2' => 'خصم',
            default => '—',
        };
        return [
            $i,
            $row->employee->employee_name_A ?? '—',
            $row->employee->employee_id    ?? '—',
            $row->kpi->name                ?? '—',
            self::$categoryLabels[$row->kpi->category ?? ''] ?? ($row->kpi->category ?? '—'),
            $row->kpi->measurement_unit    ?? '—',
            $row->kpi->target_value        ?? 0,
            $row->actual_value,
            $row->achievement_pct,
            $row->kpi->weight              ?? 0,
            $row->score,
            $row->salary_effect_amount     ?? 0,
            $direction,
        ];
    }

    public function title(): string { return 'مؤشرات الأداء KPI'; }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '4e1f88']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach ($sheet->getRowIterator(2) as $row) {
            $idx = $row->getRowIndex();
            $pct = (float) $sheet->getCell('I' . $idx)->getValue();
            $color = match (true) {
                $pct >= 100 => 'd4edda',
                $pct >= 80  => 'd1ecf1',
                $pct >= 60  => 'fff3cd',
                default     => 'f8d7da',
            };
            $sheet->getStyle("A{$idx}:M{$idx}")->getFill()
                ->setFillType('solid')->getStartColor()->setRGB($color);
        }

        return [];
    }
}
