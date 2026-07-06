<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Database\QueryException;

class EmployeeNidImport implements ToCollection, WithStartRow
{
    protected int $comCode;
    public int $updated   = 0;
    public int $notFound  = 0;
    public int $skipped   = 0;
    public int $errors    = 0;
    public array $errorDetails = [];

    public function __construct(int $comCode)
    {
        $this->comCode = $comCode;
    }

    public function startRow(): int
    {
        return 2;
    }

    private function cleanExcelStr($val): string
    {
        $s = trim((string) ($val ?? ''));
        if (preg_match('/^=""(.+)""$/', $s, $m)) {
            return $m[1];
        }
        if (preg_match('/^="(.+)"$/', $s, $m)) {
            return $m[1];
        }
        return $s;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $empCode = $this->cleanExcelStr($row[0] ?? '');
            $nid     = $this->cleanExcelStr($row[1] ?? '');

            if (empty($empCode) || empty($nid)) {
                $this->skipped++;
                continue;
            }

            // Detect scientific notation (e.g. 2.98E+13) — the real NID digits are lost
            if (preg_match('/^\d+\.?\d*[Ee][+\-]?\d+$/', $nid)) {
                $this->errors++;
                $this->errorDetails[] = "كود {$empCode}: الرقم القومي بتدوين علمي ({$nid}) — يرجى فتح الملف بـ Notepad وليس Excel";
                continue;
            }

            $employee = Employee::where('com_code', $this->comCode)
                ->where('employee_id', $empCode)
                ->first();

            if (!$employee) {
                $this->notFound++;
                continue;
            }

            try {
                $employee->update(['national_id' => $nid]);
                $this->updated++;
            } catch (QueryException $e) {
                $this->errors++;
                // Duplicate NID
                if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate')) {
                    $this->errorDetails[] = "كود {$empCode}: الرقم القومي ({$nid}) مكرر مع موظف آخر";
                } else {
                    $this->errorDetails[] = "كود {$empCode}: خطأ في الحفظ";
                }
            }
        }
    }
}
