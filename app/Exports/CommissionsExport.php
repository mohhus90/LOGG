<?php

namespace App\Exports;

use App\Models\Commission;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class CommissionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected array $filters;
    protected int   $comCode;

    private static array $monthNames = [
        1  => 'يناير',  2  => 'فبراير', 3  => 'مارس',    4  => 'أبريل',
        5  => 'مايو',   6  => 'يونيو',  7  => 'يوليو',   8  => 'أغسطس',
        9  => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر',  12 => 'ديسمبر',
    ];

    private static array $statusLabels = ['1' => 'معتمدة', '2' => 'معلقة', '3' => 'ملغاة'];

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->comCode = (int) Auth::guard('admin')->user()->com_code;
    }

    public function query()
    {
        $q = Commission::with('employee')->where('com_code', $this->comCode);

        if (!empty($this->filters['employee_id']))     $q->where('employee_id',     $this->filters['employee_id']);
        if (!empty($this->filters['month']))           $q->where('month',           $this->filters['month']);
        if (!empty($this->filters['year']))            $q->where('year',            $this->filters['year']);
        if (!empty($this->filters['status']))          $q->where('status',          $this->filters['status']);
        if (!empty($this->filters['commission_type'])) $q->where('commission_type', $this->filters['commission_type']);

        match ($this->filters['sort_by'] ?? 'date_desc') {
            'amount_asc'  => $q->orderBy('amount', 'asc'),
            'amount_desc' => $q->orderBy('amount', 'desc'),
            'month_asc'   => $q->orderBy('year', 'asc')->orderBy('month', 'asc'),
            'month_desc'  => $q->orderBy('year', 'desc')->orderBy('month', 'desc'),
            'date_asc'    => $q->orderBy('commission_date', 'asc'),
            default       => $q->orderBy('commission_date', 'desc'),
        };

        return $q;
    }

    public function headings(): array
    {
        return ['م', 'اسم الموظف', 'رقم الموظف', 'نوع العمولة', 'المبلغ (ج.م)', 'الشهر', 'السنة', 'تاريخ الإضافة', 'الحالة', 'ملاحظات'];
    }

    public function map($row): array
    {
        static $i = 0; $i++;
        return [
            $i,
            $row->employee->employee_name_A ?? '—',
            $row->employee->employee_id    ?? '—',
            $row->commission_type ?? '—',
            $row->amount,
            self::$monthNames[$row->month] ?? $row->month,
            $row->year,
            $row->commission_date,
            self::$statusLabels[(string)$row->status] ?? '',
            $row->notes ?? '',
        ];
    }

    public function title(): string { return 'العمولات'; }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a6f3c']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        foreach ($sheet->getRowIterator(2) as $row) {
            $idx    = $row->getRowIndex();
            $status = $sheet->getCell('I' . $idx)->getValue();
            $color  = match ($status) {
                'معتمدة' => 'd4edda',
                'معلقة'  => 'fff3cd',
                'ملغاة'  => 'f8d7da',
                default  => null,
            };
            if ($color) {
                $sheet->getStyle("A{$idx}:J{$idx}")->getFill()
                    ->setFillType('solid')->getStartColor()->setRGB($color);
            }
        }

        return [];
    }
}
