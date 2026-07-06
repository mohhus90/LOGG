<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $guarded = [];

    public function invoices()  { return $this->hasMany(SalesInvoice::class, 'customer_id'); }
    public function orders()    { return $this->hasMany(SalesOrder::class, 'customer_id'); }
    public function payments()  { return $this->hasMany(SalesPayment::class, 'customer_id'); }
    public function quotations(){ return $this->hasMany(SalesQuotation::class, 'customer_id'); }

    public function getTotalDebtAttribute(): float
    {
        return (float) $this->invoices()->whereIn('payment_status', ['unpaid','partial'])->sum('remaining_amount');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'company' ? 'شركة' : 'فرد';
    }
}
