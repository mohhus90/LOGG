<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
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
        $q = Attendance::with(['employee', 'shift'])
            ->where('com_code', $this->comCode);

        if (!empty($this->filters['employee_id']))
            $q->where('employee_id', $this->filters['employee_id']);
        if (!empty($this->filters['from_date']))
            $q->where('attendance_date', '>=', $this->filters['from_date']);
        if (!empty($this->filters['to_date']))
            $q->where('attendance_date', '<=', $this->filters['to_date']);
        if (isset($this->filters['status']) && $this->filters['status'] !== '')
            $q->where('status', $this->filters['status']);

        return $q->orderBy('attendance_date', 'desc');
    }

    public function headings(): array
    {
        return [
            'م', 'اسم الموظف', 'رقم الموظف', 'التاريخ', 'الحضور', 'الانصراف',
            'الحالة', 'تأخير (د)', 'أوفرتايم (س)', 'خصم التأخير', 'قيمة الأوفرتايم', 'ملاحظات',
        ];
    }

    public function map($row): array
    {
        static $i = 0;
        $i++;

        $statuses = [1 => 'حاضر', 2 => 'غائب', 3 => 'إجازة', 4 => 'إجازة رسمية', 5 => 'مأمورية'];

        return [
            $i,
            $row->employee->employee_name_A ?? '—',
            $row->employee->employee_id    ?? '—',
            $row->attendance_date,
            $row->check_in_time  ?? '—',
            $row->check_out_time ?? '—',
            $statuses[$row->status] ?? '—',
            $row->late_minutes    ?? 0,
            $row->overtime_hours  ?? 0,
            $row->late_deduction  ?? 0,
            $row->overtime_amount ?? 0,
            $row->notes           ?? '',
        ];
    }

    public function title(): string { return 'سجلات الحضور'; }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '2b6cb0']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        return [];
    }
}
