<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    protected $table = 'sales_invoice_items';
    protected $guarded = [];

    public function invoice() { return $this->belongsTo(SalesInvoice::class, 'invoice_id'); }
    public function item()    { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()    { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
