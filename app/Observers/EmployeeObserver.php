<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeSalaryHistory;

class EmployeeObserver
{
    /** Request-scoped context set by callers right before a salary-affecting save. */
    public static ?string $source = null;
    public static ?int $ruleId = null;
    public static ?string $method = null;
    public static $changeValue = null;
    public static ?string $effectiveDate = null;

    /**
     * Run $callback with salary-history context applied to every Employee save inside it,
     * then reset the context regardless of outcome.
     */
    public static function withContext(array $context, callable $callback)
    {
        self::$source        = $context['source'] ?? null;
        self::$ruleId         = $context['ruleId'] ?? null;
        self::$method         = $context['method'] ?? null;
        self::$changeValue    = $context['changeValue'] ?? null;
        self::$effectiveDate  = $context['effectiveDate'] ?? null;

        try {
            return $callback();
        } finally {
            self::$source = self::$ruleId = self::$method = self::$changeValue = self::$effectiveDate = null;
        }
    }

    public function updating(Employee $employee): void
    {
        if (!$employee->isDirty('emp_sal')) {
            return;
        }

        $original = (float) ($employee->getOriginal('emp_sal') ?? 0);
        $new      = (float) ($employee->emp_sal ?? 0);

        if ($original === $new) {
            return;
        }

        EmployeeSalaryHistory::create([
            'com_code'                => $employee->com_code,
            'employee_id'             => $employee->id,
            'old_salary'              => $original,
            'new_salary'              => $new,
            'effective_date'          => self::$effectiveDate ?? now()->toDateString(),
            'method'                  => self::$method ?? 'manual',
            'change_value'            => self::$changeValue,
            'salary_increase_rule_id' => self::$ruleId,
            'source'                  => self::$source ?? 'manual_edit',
            'added_by'                => auth()->guard('admin')->id(),
        ]);
    }
}
