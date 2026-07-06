<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    protected $table = 'journal_entry_lines';
    protected $guarded = [];

    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
    public function account()      { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
    public function costCenter()   { return $this->belongsTo(CostCenter::class, 'cost_center_id'); }

    /** يحل اسم الطرف (عميل/مورد/موظف) دون الحاجة لعلاقة morphTo حقيقية */
    public function getPartyNameAttribute(): ?string
    {
        if (!$this->party_type || !$this->party_id) return null;
        return match ($this->party_type) {
            'customer' => Customer::find($this->party_id)?->name,
            'supplier' => Supplier::find($this->party_id)?->name,
            'employee' => Employee::find($this->party_id)?->employee_name_A,
            default    => null,
        };
    }
}
