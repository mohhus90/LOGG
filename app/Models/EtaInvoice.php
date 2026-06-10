<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtaInvoice extends Model
{
    protected $fillable = [
        'com_code', 'direction', 'uuid', 'long_id', 'internal_id',
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
