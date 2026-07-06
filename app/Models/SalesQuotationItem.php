<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesQuotationItem extends Model
{
    protected $table = 'sales_quotation_items';
    protected $guarded = [];

    public function quotation() { return $this->belongsTo(SalesQuotation::class, 'quotation_id'); }
    public function item()      { return $this->belongsTo(Item::class, 'item_id'); }
    public function unit()      { return $this->belongsTo(ItemUnit::class, 'unit_id'); }
}
