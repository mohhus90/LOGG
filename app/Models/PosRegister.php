<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosRegister extends Model
{
    protected $table = 'pos_registers';
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function cashBox()   { return $this->belongsTo(CashBox::class, 'cash_box_id'); }
    public function warehouse() { return $this->belongsTo(Warehouse::class, 'warehouse_id'); }
    public function branch()    { return $this->belongsTo(Branche::class, 'branch_id'); }
    public function sessions()  { return $this->hasMany(PosSession::class, 'register_id'); }
}
