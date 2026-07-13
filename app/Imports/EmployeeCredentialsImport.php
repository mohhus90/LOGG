<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Database\QueryException;

/**
 * Updates only login_username/login_password for existing employees, matched by
 * employee_id (column B of the standard employees export). Reads the last two
 * columns of each row as username/password, regardless of how many columns
 * precede them — matches the "Employee.xlsx" export layout produced by EmployeeExport.
 */
class EmployeeCredentialsImport implements ToCollection, WithStartRow
{
    protected int $comCode;
    public int $updated  = 0;
    public int $notFound = 0;
    public int $skipped  = 0;
    public int $errors   = 0;
    public array $errorDetails = [];

    public function __construct(int $comCode)
    {
        $this->comCode = $comCode;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $row = $row->toArray();
            $employeeId = trim((string) ($row[1] ?? ''));

            if ($employeeId === '') {
                $this->skipped++;
                continue;
            }

            $lastIndex = count($row) - 1;
            $password  = trim((string) ($row[$lastIndex] ?? ''));
            $username  = trim((string) ($row[$lastIndex - 1] ?? ''));

            if ($username === '' && $password === '') {
                $this->skipped++;
                continue;
            }

            $employee = Employee::where('com_code', $this->comCode)
                ->where('employee_id', $employeeId)
                ->first();

            if (!$employee) {
                $this->notFound++;
                continue;
            }

            $data = [];
            if ($username !== '') $data['login_username'] = $username;
            if ($password !== '') $data['login_password'] = $password;

            try {
                $employee->update($data);
                $this->updated++;
            } catch (QueryException $e) {
                $this->errors++;
                if (str_contains($e->getMessage(), '1062') || str_contains($e->getMessage(), 'Duplicate')) {
                    $this->errorDetails[] = "كود {$employeeId}: اسم المستخدم ({$username}) مكرر مع موظف آخر";
                } else {
                    $this->errorDetails[] = "كود {$employeeId}: خطأ في الحفظ";
                }
            }
        }
    }
}
