<?php

namespace App\Exports;

use App\Models\EtaInvoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EtaInvoicesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private array $filters, private int $comCode) {}

    public function query()
    {
        $direction = $this->filters['direction'] ?? 'Sent';

        $q = EtaInvoice::where('com_code', $this->comCode)
                ->where('direction', $direction);

        if (!empty($this->filters['status']))    $q->where('status', $this->filters['status']);
        if (!empty($this->filters['doc_type']))  $q->where('document_type', $this->filters['doc_type']);
        if (isset($this->filters['is_posted']) && $this->filters['is_posted'] !== '')
            $q->where('is_posted', (bool) $this->filters['is_posted']);
        if (!empty($this->filters['from']))
            $q->whereDate('date_issued', '>=', $this->filters['from']);
        if (!empty($this->filters['to']))
            $q->whereDate('date_issued', '<=', $this->filters['to']);

        return $q->orderByDesc('date_issued');
    }

    public function headings(): array
    {
        return [
            'م', 'UUID', 'الرقم الداخلي', 'النوع',
            'البائع', 'الرقم الضريبي للبائع',
            'المشتري', 'الرقم الضريبي للمشتري',
            'تاريخ الإصدار',
            'إجمالي المبيعات (ج.م)', 'إجمالي الخصم (ج.م)', 'صافي المبلغ (ج.م)',
            'ضريبة القيمة المضافة T1 (ج.م)',
            'ضريبة جدولية T2 (ج.م)',
            'خصم تحت حساب الضريبة T4 (ج.م)',
            'إجمالي المبلغ (ج.م)',
            'الحالة', 'مرحّل محاسبياً',
        ];
    }

    public function map($row): array
    {
        static $i = 0; $i++;

        $docTypes = ['I' => 'فاتورة', 'C' => 'إشعار دائن', 'D' => 'إشعار مدين'];
        $statuses = [
            'Valid'     => 'معتمدة',
            'Invalid'   => 'غير صالحة',
            'Cancelled' => 'ملغاة',
            'Submitted' => 'مرسلة',
            'Rejected'  => 'مرفوضة',
        ];

        // استخراج تفاصيل الضرائب من raw_data إن وُجدت
        $raw       = $row->raw_data ?? [];
        $taxTotals = $raw['taxTotals'] ?? [];
        $t1 = $t2 = $t4 = 0.0;
        foreach ($taxTotals as $tax) {
            $type = $tax['taxType'] ?? '';
            $amt  = (float)($tax['amount'] ?? 0);
            if ($type === 'T1')                          $t1 += $amt;
            elseif ($type === 'T2')                      $t2 += $amt;
            elseif (in_array($type, ['T4','W1','W11']))  $t4 += abs($amt);
        }

        // fallback: لو taxTotals فارغة استخدم total_vat كـ T1
        if ($t1 == 0 && $t2 == 0 && $t4 == 0 && $row->total_vat != 0) {
            $t1 = (float) $row->total_vat;
        }

        return [
            $i,
            $row->uuid,
            $row->internal_id ?? '—',
            $docTypes[$row->document_type] ?? $row->document_type,
            $row->issuer_name   ?? '—',
            $row->issuer_id     ?? '—',
            $row->receiver_name ?? '—',
            $row->receiver_id   ?? '—',
            $row->date_issued?->format('Y-m-d') ?? '—',
            number_format($row->total_sales,    2),
            number_format($row->total_discount, 2),
            number_format($row->net_amount,     2),
            number_format($t1, 2),
            number_format($t2, 2),
            number_format($t4, 2),
            number_format($row->total_amount,   2),
            $statuses[$row->status] ?? $row->status,
            $row->is_posted ? 'نعم' : 'لا',
        ];
    }

    public function title(): string
    {
        $direction = $this->filters['direction'] ?? 'Sent';
        return $direction === 'Sent' ? 'فواتير المبيعات' : 'فواتير المشتريات';
    }

    public function styles(Worksheet $sheet): array
    {
        // R = عمود 18 (عدد الأعمدة الجديد)
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a6b3c']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        return [];
    }
}
