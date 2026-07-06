<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    protected $table = 'project_tasks';
    protected $guarded = [];
    protected $casts = ['due_date' => 'date'];

    private static $statusLabels   = ['todo' => ['قيد الانتظار', 'secondary'], 'in_progress' => ['قيد التنفيذ', 'warning'], 'done' => ['منجزة', 'success']];
    private static $priorityLabels = ['low' => ['منخفضة', 'secondary'], 'medium' => ['متوسطة', 'info'], 'high' => ['عالية', 'danger']];

    public function project()    { return $this->belongsTo(Project::class, 'project_id'); }
    public function assignee()   { return $this->belongsTo(Employee::class, 'assigned_to'); }
    public function createdBy()  { return $this->belongsTo(Admin::class, 'created_by'); }

    public function getStatusLabelAttribute(): string { return self::$statusLabels[$this->status][0] ?? $this->status; }
    public function getStatusColorAttribute(): string { return self::$statusLabels[$this->status][1] ?? 'secondary'; }
    public function getPriorityLabelAttribute(): string { return self::$priorityLabels[$this->priority][0] ?? $this->priority; }
    public function getPriorityColorAttribute(): string { return self::$priorityLabels[$this->priority][1] ?? 'secondary'; }

    public static function statusOptions(): array { return self::$statusLabels; }
}
