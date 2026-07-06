<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cheque extends Model
{
    protected $table = 'cheques';
    protected $guarded = [];
    protected $casts = ['cheque_date' => 'date', 'due_date' => 'date', 'collected_at' => 'date', 'bounced_at' => 'date', 'amount' => 'float'];

    private static $statusLabels = [
        'under_collection' => ['تحت التحصيل', 'warning'],
        'collected'         => ['تم التحصيل', 'success'],
        'bounced'           => ['مرتجع', 'danger'],
        'cancelled'         => ['ملغي', 'secondary'],
    ];

    public function bankAccount()     { return $this->belongsTo(BankAccount::class, 'bank_account_id'); }
    public function treasuryVoucher() { return $this->belongsTo(TreasuryVoucher::class, 'treasury_voucher_id'); }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }

    public function getPartyNameAttribute(): ?string
    {
        return match ($this->party_type) {
            'customer' => Customer::find($this->party_id)?->name,
            'supplier' => Supplier::find($this->party_id)?->name,
            default    => null,
        };
    }
}
