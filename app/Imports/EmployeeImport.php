<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
class EmployeeImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            Employee::create([
                'added_by' => auth()->guard('admin')->user()->id,
                'com_code' => auth()->guard('admin')->user()->com_code,
                'employee_id' => $row[0],
                'finger_id' => $row[1],
                'employee_name' => $row[2],
                'employee_adress' =>$row[3],
                'emp_gender' => $row[4],
                'emp_social_status' => $row[5],
                'emp_start_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[6]),
                'functional_status' => $row[7],
                'resignation_status' => $row[8],
                'qualification_grade' => $row[9],
                'emp_military_status' => $row[10],
                'mtivation_type' => $row[11],
                'mtivation' => $row[12],
                'sal_cash_visa' => $row[13],
                'bank_name' => $row[14],
                'bank_account' =>$row[15],
                'bank_ID' => $row[16],
                'bank_branch' => $row[17],
                'daily_work_hours' => $row[18],
                'emp_departments_id' => $row[19],
                'emp_jobs_id' => $row[20],
                'shifts_types_id' => $row[21],
                'branches_id' => $row[22],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            
        }
    }
}