<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryIncreaseRule extends Model
{
    protected $table = 'salary_increase_rules';
    protected $guarded = [];

    /** Scope precedence when several rules could apply to the same employee — most specific first. */
    public const SCOPE_PRECEDENCE = ['employee', 'client', 'department', 'branch', 'job', 'global'];

    public const SCOPE_LABELS = [
        'global'     => 'كل الموظفين (افتراضي عام)',
        'client'     => 'عميل معيّن',
        'department' => 'إدارة معيّنة',
        'branch'     => 'فرع معيّن',
        'job'        => 'وظيفة معيّنة',
        'employee'   => 'موظف معيّن',
    ];

    public function history()
    {
        return $this->hasMany(EmployeeSalaryHistory::class, 'salary_increase_rule_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(Admin::class, 'added_by');
    }

    /** Employees matched by this rule's scope, within the given company. */
    public function matchedEmployeesQuery(int $comCode)
    {
        $query = Employee::where('com_code', $comCode);

        return match ($this->scope_type) {
            'employee'   => $query->where('id', $this->scope_id),
            'client'     => $query->where('client_id', $this->scope_id),
            'department' => $query->where('emp_departments_id', $this->scope_id),
            'branch'     => $query->where('branches_id', $this->scope_id),
            'job'        => $query->where('emp_jobs_id', $this->scope_id),
            default      => $query, // global
        };
    }

    public function computeNewSalary(float $currentSalary): float
    {
        if ($this->method === 'percentage') {
            return round($currentSalary * (1 + ((float) $this->value / 100)), 2);
        }

        return round($currentSalary + (float) $this->value, 2);
    }

    public function scopeLabel(): string
    {
        return self::SCOPE_LABELS[$this->scope_type] ?? $this->scope_type;
    }

    /** Resolve the single highest-precedence active rule applicable to a given employee (employee > client > department > branch > job > global). */
    public static function resolveForEmployee(int $comCode, Employee $employee): ?self
    {
        foreach (self::SCOPE_PRECEDENCE as $scopeType) {
            $scopeId = match ($scopeType) {
                'employee'   => $employee->id,
                'client'     => $employee->client_id,
                'department' => $employee->emp_departments_id,
                'branch'     => $employee->branches_id,
                'job'        => $employee->emp_jobs_id,
                default      => null, // global
            };

            if ($scopeType !== 'global' && empty($scopeId)) {
                continue;
            }

            $rule = self::where('com_code', $comCode)
                ->where('scope_type', $scopeType)
                ->where('scope_id', $scopeId)
                ->where('status', 1)
                ->where('effective_date', '<=', now()->toDateString())
                ->orderByDesc('effective_date')
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        return null;
    }
}
