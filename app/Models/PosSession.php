<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosSession extends Model
{
    protected $table = 'pos_sessions';
    protected $guarded = [];
    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];

    public function register() { return $this->belongsTo(PosRegister::class, 'register_id'); }
    public function openedBy() { return $this->belongsTo(Admin::class, 'opened_by'); }
    public function invoices() { return $this->hasMany(SalesInvoice::class, 'pos_session_id'); }

    public function getSalesTotalAttribute(): float
    {
        return (float) $this->invoices()->where('status', 'issued')->sum('total');
    }
}
