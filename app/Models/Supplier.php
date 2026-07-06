<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'suppliers';
    protected $guarded = [];

    public function invoices() { return $this->hasMany(PurchaseInvoice::class, 'supplier_id'); }
    public function orders()   { return $this->hasMany(PurchaseOrder::class, 'supplier_id'); }
    public function payments() { return $this->hasMany(PurchasePayment::class, 'supplier_id'); }

    public function getTotalDebtAttribute(): float
    {
        return (float) $this->invoices()->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'company' ? 'شركة' : 'فرد';
    }
}
