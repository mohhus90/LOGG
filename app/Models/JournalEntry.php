<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'journal_entries';
    protected $guarded = [];

    protected static $statusLabels = [
        'draft'    => ['مسودة', 'secondary'],
        'posted'   => ['مرحّل', 'success'],
        'reversed' => ['معكوس', 'danger'],
    ];

    public function lines()         { return $this->hasMany(JournalEntryLine::class); }
    public function reversedEntry() { return $this->belongsTo(JournalEntry::class, 'reversed_entry_id'); }
    public function period()        { return $this->belongsTo(AccountingPeriod::class, 'period_id'); }
    public function createdBy()     { return $this->belongsTo(Admin::class, 'created_by'); }
    public function postedBy()      { return $this->belongsTo(Admin::class, 'posted_by'); }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status][0] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusLabels[$this->status][1] ?? 'secondary';
    }
}
