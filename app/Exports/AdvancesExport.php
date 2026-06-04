<?php

namespace App\Exports;

use App\Models\Advance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class AdvancesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
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
        $q = Advance::with('employee')
            ->where('com_code', $this->comCode);

        if (!empty($this->filters['employee_id']))
            $q->where('employee_id', $this->filters['employee_id']);
        if (!empty($this->filters['from_date']))
            $q->where('advance_date', '>=', $this->filters['from_date']);
        if (!empty($this->filters['to_date']))
            $q->where('advance_date', '<=', $this->filters['to_date']);

        return $q->orderBy('advance_date', 'desc');
    }

    public function headings(): array
    {
        return ['م', 'اسم الموظف', 'رقم الموظف', 'المبلغ', 'التاريخ', 'ملاحظات'];
    }

    public function map($row): array
    {
        static $i = 0; $i++;
        return [
            $i,
            $row->employee->employee_name_A ?? '—',
            $row->employee->employee_id    ?? '—',
            $row->amount,
            $row->advance_date,
            $row->notes ?? '',
        ];
    }

    public function title(): string { return 'السلف'; }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => 'e6704a']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        return [];
    }
}
