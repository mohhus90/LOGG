<?php

namespace App\Exports;

use App\Models\MonthlyPayroll;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PayrollExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $filters;
    protected int   $comCode;
    protected bool  $hasBonuses;
    protected bool  $hasExtendedFields;

    private static array $monthNames = [
        1=>'يناير', 2=>'فبراير', 3=>'مارس', 4=>'أبريل',
        5=>'مايو', 6=>'يونيو', 7=>'يوليو', 8=>'أغسطس',
        9=>'سبتمبر', 10=>'أكتوبر', 11=>'نوفمبر', 12=>'ديسمبر',
    ];

    private static array $statusLabels = [
        '1' => 'مسودة', '2' => 'معتمد', '3' => 'مدفوع',
    ];

    public function __construct(array $filters = [])
    {
        $this->filters    = $filters;
        $this->comCode    = (int) Auth::guard('admin')->user()->com_code;
        $this->hasBonuses = Schema::hasColumn('monthly_payrolls', 'bonuses_amount');
        $this->hasExtendedFields = Schema::hasColumn('monthly_payrolls', 'weekly_off_days');
    }

    public function query()
    {
        $q = MonthlyPayroll::with(['employee.branches'])
            ->where('monthly_payrolls.com_code', $this->comCode);

        if (!empty($this->filters['month']))       $q->where('month', $this->filters['month']);
        if (!empty($this->filters['year']))        $q->where('year',  $this->filters['year']);
        if (!empty($this->filters['status']))      $q->where('status', $this->filters['status']);
        if (!empty($this->filters['employee_id'])) $q->where('monthly_payrolls.employee_id', $this->filters['employee_id']);
        if (!empty($this->filters['branch_id'])) {
            $q->whereHas('employee', fn($eq) => $eq->where('branches_id', $this->filters['branch_id']));
        }

        $sort = $this->filters['sort_by'] ?? 'name_asc';
        match ($sort) {
            'net_desc'   => $q->orderBy('net_salary', 'desc'),
            'net_asc'    => $q->orderBy('net_salary', 'asc'),
            'gross_desc' => $q->orderBy('gross_salary', 'desc'),
            'gross_asc'  => $q->orderBy('gross_salary', 'asc'),
            'month_desc' => $q->orderBy('year', 'desc')->orderBy('month', 'desc'),
            'month_asc'  => $q->orderBy('year', 'asc')->orderBy('month', 'asc'),
            'name_desc'  => $q->join('employees', 'monthly_payrolls.employee_id', '=', 'employees.id')
                              ->orderBy('employees.employee_name_A', 'desc')
                              ->select('monthly_payrolls.*'),
            default      => $q->join('employees', 'monthly_payrolls.employee_id', '=', 'employees.id')
                              ->orderBy('employees.employee_name_A', 'asc')
                              ->select('monthly_payrolls.*'),
        };

        return $q;
    }

    public function headings(): array
    {
        $heads = [
            'م', 'اسم الموظف', 'رقم الموظف', 'الفرع', 'الشهر', 'السنة',
            'الفترة من', 'الفترة إلى',
            'أيام العمل', 'أيام الغياب', 'أيام الإجازة',
        ];
        if ($this->hasExtendedFields) $heads[] = 'أيام الإجازة الأسبوعية';
        $heads = array_merge($heads, [
            'الراتب الأساسي', 'الراتب المستحق', 'البدلات الثابتة',
            'الأوفرتايم', 'العمولات',
        ]);
        if ($this->hasBonuses) $heads[] = 'المكافآت';
        if ($this->hasExtendedFields) {
            $heads[] = 'بدل الإجازة الأسبوعية';
            $heads[] = 'مكافأة KPI';
        }
        $heads = array_merge($heads, [
            'خصم التأخير', 'خصم الغياب', 'خصومات أخرى',
            'قسط السلفة', 'خصم التأمين',
        ]);
        if ($this->hasExtendedFields) {
            $heads[] = 'خصم KPI';
            $heads[] = 'خصم الجزاءات';
        }
        $heads = array_merge($heads, [
            'إجمالي الخصومات',
            'الراتب الإجمالي', 'الراتب الصافي', 'الحالة', 'ملاحظات',
        ]);
        return $heads;
    }

    public function map($p): array
    {
        static $i = 0; $i++;

        $totalDeductions = $p->late_deductions + $p->absence_deductions
            + $p->deductions_amount + $p->advance_installment + $p->insurance_deduction
            + ($p->kpi_deduction_amount ?? 0) + ($p->sanctions_deduction ?? 0);

        $row = [
            $i,
            $p->employee->employee_name_A   ?? '—',
            $p->employee->employee_id       ?? '—',
            $p->employee->branches->branch_name ?? '—',
            self::$monthNames[$p->month]    ?? $p->month,
            $p->year,
            $p->period_from,
            $p->period_to,
            $p->work_days,
            $p->absence_days,
            $p->leave_days,
        ];

        if ($this->hasExtendedFields) $row[] = $p->weekly_off_days ?? 0;

        $row = array_merge($row, [
            $p->basic_salary,
            $p->earned_salary,
            $p->fixed_allowances,
            $p->overtime_amount,
            $p->commissions_amount,
        ]);

        if ($this->hasBonuses) $row[] = $p->bonuses_amount ?? 0;
        if ($this->hasExtendedFields) {
            $row[] = $p->leave_compensation_amount ?? 0;
            $row[] = $p->kpi_bonus_amount ?? 0;
        }

        $row = array_merge($row, [
            $p->late_deductions,
            $p->absence_deductions,
            $p->deductions_amount,
            $p->advance_installment,
            $p->insurance_deduction,
        ]);

        if ($this->hasExtendedFields) {
            $row[] = $p->kpi_deduction_amount ?? 0;
            $row[] = $p->sanctions_deduction ?? 0;
        }

        $row = array_merge($row, [
            $totalDeductions,
            $p->gross_salary,
            $p->net_salary,
            self::$statusLabels[(string)$p->status] ?? '',
            $p->notes ?? '',
        ]);

        return $row;
    }

    public function title(): string { return 'كشف الرواتب'; }

    public function styles(Worksheet $sheet): array
    {
        $totalCols     = count($this->headings());
        $lastCol       = Coordinate::stringFromColumnIndex($totalCols);
        $statusColIdx  = $totalCols - 1; // آخر عمود قبل الملاحظات
        $statusCol     = Coordinate::stringFromColumnIndex($statusColIdx);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a3c6e']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        // تلوين الصفوف حسب الحالة
        foreach ($sheet->getRowIterator(2) as $row) {
            $idx    = $row->getRowIndex();
            $status = $sheet->getCell($statusCol . $idx)->getValue();
            $color  = match ($status) {
                'مسودة'  => 'fff9e6',
                'معتمد'  => 'd4edda',
                'مدفوع'  => 'cce5ff',
                default  => null,
            };
            if ($color) {
                $sheet->getStyle("A{$idx}:{$lastCol}{$idx}")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB($color);
            }
        }

        // تنسيق أعمدة الأرقام: من عمود الراتب الأساسي حتى ما قبل عمودي الحالة والملاحظات
        for ($col = 12; $col <= $statusColIdx - 1; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("{$colLetter}2:{$colLetter}9999")->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        return [];
    }
}
