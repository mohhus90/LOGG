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
            'المُصدر', 'الرقم الضريبي للمُصدر',
            'المستلم', 'الرقم الضريبي للمستلم',
            'تاريخ الإصدار',
            'إجمالي المبيعات', 'إجمالي الخصم', 'صافي المبلغ',
            'قيمة الضريبة', 'الإجمالي',
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
            number_format($row->total_vat,      2),
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
        $sheet->getStyle('A1:P1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a6b3c']],
            'alignment' => ['horizontal' => 'center'],
        ]);
        return [];
    }
}
