<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'projects';
    protected $guarded = [];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'budget' => 'float'];

    private static $statusLabels = [
        'planning'  => ['تخطيط', 'secondary'],
        'active'    => ['نشط', 'success'],
        'on_hold'   => ['متوقف', 'warning'],
        'completed' => ['مكتمل', 'primary'],
        'cancelled' => ['ملغي', 'danger'],
    ];

    public function customer() { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function tasks()    { return $this->hasMany(ProjectTask::class, 'project_id'); }
    public function createdBy(){ return $this->belongsTo(Admin::class, 'created_by'); }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }

    public static function statusOptions(): array { return self::$statusLabels; }
}
