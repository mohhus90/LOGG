<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreasuryVoucher extends Model
{
    protected $table = 'treasury_vouchers';
    protected $guarded = [];
    protected $casts = ['date' => 'date', 'amount' => 'float'];

    private static $typeLabels   = ['receipt' => 'سند قبض', 'payment' => 'سند صرف'];
    private static $statusLabels = ['draft' => 'مسودة', 'posted' => 'مرحّل', 'cancelled' => 'ملغي'];

    public function cashBox()       { return $this->belongsTo(CashBox::class, 'cash_box_id'); }
    public function bankAccount()   { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
    public function cheque()        { return $this->belongsTo(Cheque::class, 'cheque_id'); }
    public function createdBy()     { return $this->belongsTo(Admin::class, 'created_by'); }

    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->voucher_type] ?? $this->voucher_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    /** يحل اسم الطرف (عميل/مورد/موظف/آخر) دون علاقة morphTo حقيقية */
    public function getPartyNameAttribute(): ?string
    {
        return match ($this->party_type) {
            'customer' => Customer::find($this->party_id)?->name,
            'supplier' => Supplier::find($this->party_id)?->name,
            'employee' => Employee::find($this->party_id)?->employee_name_A,
            default    => 'أخرى',
        };
    }
}
