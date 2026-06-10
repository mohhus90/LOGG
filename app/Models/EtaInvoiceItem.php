<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtaInvoiceItem extends Model
{
    protected $fillable = [
        'eta_invoice_id', 'item_code', 'description', 'unit_type',
        'quantity', 'unit_price', 'total', 'discount',
        'net_total', 'vat_rate', 'vat_amount', 'total_with_vat',
    ];

    protected $casts = [
        'quantity'      => 'decimal:4',
        'unit_price'    => 'decimal:4',
        'total'         => 'decimal:2',
        'discount'      => 'decimal:2',
        'net_total'     => 'decimal:2',
        'vat_rate'      => 'decimal:2',
        'vat_amount'    => 'decimal:2',
        'total_with_vat' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(EtaInvoice::class, 'eta_invoice_id');
    }
}
