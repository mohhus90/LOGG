<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping,
    ShouldAutoSize, WithStyles, WithTitle, WithColumnFormatting
{
    protected array  $filters;
    protected int    $comCode;
    protected string $sortBy;

    private static int $rowIndex = 0;

    protected array $dayNames = [
        'Saturday'  => 'السبت',
        'Sunday'    => 'الأحد',
        'Monday'    => 'الاثنين',
        'Tuesday'   => 'الثلاثاء',
        'Wednesday' => 'الأربعاء',
        'Thursday'  => 'الخميس',
        'Friday'    => 'الجمعة',
    ];

    protected array $statusNames = [
        1 => 'حضر',
        2 => 'غياب',
        3 => 'إجازة',
        4 => 'إجازة رسمية',
        5 => 'مأمورية',
        6 => 'إجازة أسبوعية',
    ];

    public function __construct(array $filters = [], string $sortBy = 'date_desc')
    {
        $this->filters = $filters;
        $this->sortBy  = $sortBy;
        $this->comCode = (int) Auth::guard('admin')->user()->com_code;
        self::$rowIndex = 0;
    }

    public function collection()
    {
        $q = Attendance::with(['employee', 'shift', 'shiftOverride'])
            ->where('com_code', $this->comCode);

        if (!empty($this->filters['employee_id']))
            $q->where('attendances.employee_id', $this->filters['employee_id']);
        if (!empty($this->filters['from_date']))
            $q->where('attendance_date', '>=', $this->filters['from_date']);
        if (!empty($this->filters['to_date']))
            $q->where('attendance_date', '<=', $this->filters['to_date']);
        if (isset($this->filters['status']) && $this->filters['status'] !== '')
            $q->where('status', $this->filters['status']);
        if (!empty($this->filters['department_id'])) {
            $q->whereHas('employee', fn($sq) => $sq->where('emp_departments_id', $this->filters['department_id']));
        }

        match ($this->sortBy) {
            'date_asc'  => $q->orderBy('attendance_date', 'asc'),
            'name_asc'  => $q->join('employees', 'attendances.employee_id', '=', 'employees.id')
                              ->orderBy('employees.employee_name_A', 'asc')
                              ->orderBy('attendance_date', 'asc')
                              ->select('attendances.*'),
            'name_desc' => $q->join('employees', 'attendances.employee_id', '=', 'employees.id')
                              ->orderBy('employees.employee_name_A', 'desc')
                              ->orderBy('attendance_date', 'asc')
                              ->select('attendances.*'),
            default     => $q->orderBy('attendance_date', 'desc'),
        };

        return $q->get();
    }

    public function headings(): array
    {
        return [
            'م',
            'اسم الموظف',
            'رقم الموظف',
            'التاريخ',
            'اليوم',
            'الشيفت',
            'وقت الحضور',
            'وقت الانصراف',
            'الحالة',
            'أيام خصم الغياب',
            'تأخير',
            'انصراف مبكر',
            'أوفرتايم (س)',
            'خصم التأخير',
            'خصم الانصراف المبكر',
            'قيمة الأوفرتايم',
            'بدل إجازة',
            'ملاحظات',
        ];
    }

    public function map($row): array
    {
        self::$rowIndex++;

        $shift     = $row->effective_shift;
        $shiftStr  = $shift ? ($shift->type . ' ' . $shift->from_time . '-' . $shift->to_time) : '—';
        $dayEn     = $row->attendance_date instanceof Carbon
            ? $row->attendance_date->format('l')
            : Carbon::parse($row->attendance_date)->format('l');

        $lateDisplay  = match ((int)($row->late_fraction ?? 0)) {
            1 => 'ربع يوم', 2 => 'نصف يوم', 3 => 'يوم كامل',
            default => ($row->late_minutes ?? 0) . ' د',
        };
        $earlyDisplay = match ((int)($row->early_departure_fraction ?? 0)) {
            1 => 'ربع يوم', 2 => 'نصف يوم', 3 => 'يوم كامل', 4 => 'يوم + نصف',
            default => ($row->early_departure_minutes ?? 0) . ' د',
        };

        $absenceDays = '';
        if ($row->status == 2 && $row->absence_deduction_days !== null) {
            $absenceDays = number_format($row->absence_deduction_days, 1);
        }

        $leaveComp = ($row->is_weekly_off_worked && ($row->leave_compensation_amount ?? 0) > 0)
            ? number_format($row->leave_compensation_amount, 2)
            : '—';

        return [
            self::$rowIndex,
            $row->employee->employee_name_A ?? '—',
            $row->employee->employee_id    ?? '—',
            $row->attendance_date instanceof Carbon
                ? $row->attendance_date->format('Y-m-d')
                : $row->attendance_date,
            $this->dayNames[$dayEn] ?? $dayEn,
            $shiftStr,
            $row->check_in_time  ?? '—',
            $row->check_out_time ?? '—',
            $this->statusNames[$row->status] ?? '—',
            $absenceDays ?: '—',
            $lateDisplay,
            $earlyDisplay,
            $row->overtime_hours  ?? 0,
            number_format($row->late_deduction ?? 0, 2),
            number_format($row->early_departure_deduction ?? 0, 2),
            number_format($row->overtime_amount ?? 0, 2),
            $leaveComp,
            $row->notes ?? '',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'M' => NumberFormat::FORMAT_NUMBER_00,
            'N' => NumberFormat::FORMAT_NUMBER_00,
            'O' => NumberFormat::FORMAT_NUMBER_00,
            'P' => NumberFormat::FORMAT_NUMBER_00,
            'Q' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function title(): string { return 'تقرير الحضور والانصراف'; }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = 'R';
        // رأس الجدول
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a56a0']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
        ]);

        // الخلايا كلها وسط
        $sheet->getStyle("A1:{$lastCol}".$sheet->getHighestRow())->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // تلوين صفوف الغياب
        foreach (range(2, $sheet->getHighestRow()) as $rowNum) {
            $statusCell = $sheet->getCell('I' . $rowNum)->getValue();
            if ($statusCell === 'غياب') {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEE2E2']],
                ]);
            } elseif (in_array($statusCell, ['إجازة', 'إجازة رسمية', 'مأمورية', 'إجازة أسبوعية'])) {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF9C3']],
                ]);
            } elseif ($statusCell === 'حضر') {
                $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F0FDF4']],
                ]);
            }
        }

        // عرض ثابت لبعض الأعمدة
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(20);

        return [];
    }
}
