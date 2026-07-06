<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceItem extends Model
{
    protected $table = 'purchase_invoice_items';
    protected $guarded = [];

    public function invoice() { return $this->belongsTo(PurchaseInvoice::class, 'invoice_id'); }
    public function item()    { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()    { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
