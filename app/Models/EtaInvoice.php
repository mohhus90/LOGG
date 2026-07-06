<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtaInvoice extends Model
{
    protected $fillable = [
        'com_code', 'direction', 'uuid', 'long_id', 'internal_id',
        'sales_invoice_id', 'purchase_invoice_id',
        'document_type', 'document_type_version',
        'issuer_id', 'issuer_name', 'receiver_id', 'receiver_name',
        'date_issued', 'date_received',
        'total_sales', 'total_discount', 'net_amount', 'total_vat', 'total_amount',
        'status', 'activity_code',
        'is_posted', 'posted_at', 'posted_by', 'posting_notes',
        'raw_data',
    ];

    protected $casts = [
        'date_issued'  => 'datetime',
        'date_received' => 'datetime',
        'posted_at'    => 'datetime',
        'is_posted'    => 'boolean',
        'raw_data'     => 'array',
        'total_sales'  => 'decimal:2',
        'total_discount' => 'decimal:2',
        'net_amount'   => 'decimal:2',
        'total_vat'    => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(EtaInvoiceItem::class);
    }

    public function poster()
    {
        return $this->belongsTo(Admin::class, 'posted_by');
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    /** الفاتورة الداخلية المرتبطة (بيع أو شراء حسب الاتجاه)، أو null لو غير مربوطة بعد */
    public function linkedInvoice()
    {
        return $this->direction === 'Sent' ? $this->salesInvoice : $this->purchaseInvoice;
    }

    /**
     * مرشحات محتملة للربط اليدوي: فواتير داخلية بنفس الاتجاه ونفس الإجمالي تقريبًا
     * خلال ٣ أيام من تاريخ إصدار فاتورة ETA (لا يوجد مفتاح مطابقة مضمون بين
     * النظامين، فهذا ترشيح تقريبي يراجعه المستخدم قبل الربط الفعلي).
     */
    public function suggestedMatches()
    {
        $date = $this->date_issued;
        if ($this->direction === 'Sent') {
            return SalesInvoice::where('com_code', $this->com_code)
                ->whereBetween('total', [$this->total_amount - 1, $this->total_amount + 1])
                ->when($date, fn ($q) => $q->whereBetween('date', [$date->copy()->subDays(3), $date->copy()->addDays(3)]))
                ->limit(10)->get();
        }
        return PurchaseInvoice::where('com_code', $this->com_code)
            ->whereBetween('total', [$this->total_amount - 1, $this->total_amount + 1])
            ->when($date, fn ($q) => $q->whereBetween('date', [$date->copy()->subDays(3), $date->copy()->addDays(3)]))
            ->limit(10)->get();
    }

    public function getDirectionLabelAttribute(): string
    {
        return $this->direction === 'Sent' ? 'مبيعات' : 'مشتريات';
    }

    public function getDocTypeLabelAttribute(): string
    {
        return match($this->document_type) {
            'C'     => 'إشعار دائن',
            'D'     => 'إشعار مدين',
            default => 'فاتورة',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'Valid'     => 'معتمدة',
            'Invalid'   => 'غير صالحة',
            'Cancelled' => 'ملغاة',
            'Submitted' => 'مرسلة',
            'Rejected'  => 'مرفوضة',
            default     => $this->status,
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            'Valid'     => 'success',
            'Invalid'   => 'danger',
            'Cancelled' => 'secondary',
            'Submitted' => 'info',
            'Rejected'  => 'warning',
            default     => 'dark',
        };
    }
}
