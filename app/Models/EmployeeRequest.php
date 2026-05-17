<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRequest extends Model
{
    use HasFactory;

    protected $table   = 'employee_requests';
    protected $guarded = [];
    protected $casts   = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            0 => '<span class="badge badge-warning">⏳ قيد الانتظار</span>',
            1 => '<span class="badge badge-success">✅ مقبول</span>',
            2 => '<span class="badge badge-danger">❌ مرفوض</span>',
            3 => '<span class="badge badge-secondary">🚫 ملغي</span>',
            default => '—',
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->request_type) {
            'annual_vacation' => '🏖 إجازة اعتيادية',
            'casual_vacation' => '📅 إجازة عارضة',
            'late_permission' => '⏰ إذن تأخير',
            'early_leave'     => '🚪 إذن انصراف مبكر',
            'mission'         => '🏢 مأمورية',
            default           => $this->request_type,
        };
    }
}
