<?php

namespace App\Exports;

use App\Models\EmployeeVacationBalance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class VacationsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $filters;
    protected int   $comCode;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->comCode = (int) Auth::guard('admin')->user()->com_code;
    }

    public function query()
    {
        $q = EmployeeVacationBalance::with('employee')
            ->where('com_code', $this->comCode);

        if (!empty($this->filters['employee_id']))
            $q->where('employee_id', $this->filters['employee_id']);

        return $q->orderBy('employee_id');
    }

    public function headings(): array
    {
        return [
            'م', 'اسم الموظف', 'رقم الموظف',
            'رصيد السنوية (يوم)', 'مستنفد سنوية', 'متبقي سنوية',
            'رصيد العارضة (يوم)', 'مستنفد عارضة', 'متبقي عارضة',
            'استحقاق شهري', 'السنة',
        ];
    }

    public function map($row): array
    {
        static $i = 0; $i++;
        return [
            $i,
            $row->employee->employee_name_A ?? '—',
            $row->employee->employee_id    ?? '—',
            $row->annual_balance    ?? 0,
            $row->annual_used       ?? 0,
            $row->annual_remaining  ?? 0,
            $row->casual_balance    ?? 0,
            $row->casual_used       ?? 0,
            $row->casual_remaining  ?? 0,
            $row->monthly_accrual   ?? 0,
            $row->year              ?? date('Y'),
        ];
    }

    public function title(): string { return 'أرصدة الإجازات'; }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2f855a']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        return [];
    }
}
