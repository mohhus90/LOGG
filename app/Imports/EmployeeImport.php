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
                'employee_name_A' => $row[2],
                'employee_name_E' => $row[3],
                'employee_address' =>$row[4],
                'emp_gender' => $row[5],
                'emp_social_status' => $row[6],
                'emp_military_status' => $row[7],
                'emp_qualification' => $row[8],
                'qualification_year' => $row[9],
                'qualification_grade' => $row[10],
                'emp_start_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[11]),
                'functional_status' => $row[12],
                'insurance_status' => $row[13],
                'resignation_status' => $row[14],
                'resignation_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[15]),
                'resignation_cause' => $row[16],
                'motivation_type' => $row[17],
                'motivation' => $row[18],
                'sal_cash_visa' => $row[18],
                'bank_name' => $row[20],
                'bank_account' =>$row[21],
                'bank_ID' => $row[22],
                'bank_branch' => $row[23],
                'daily_work_hours' => $row[24],
                'emp_jobs_id' => $row[25],
                'national_id' => $row[26],
                'insurance_no' => $row[27],
                'emp_departments_id' => $row[28],
                'emp_home_tel' => $row[29],
                'emp_mobile' => $row[30],
                'emp_email' => $row[31],
                'emp_photo' => $row[32],
                'birth_date' => $row[333],
                'emp_sal' => $row[34],
                'emp_fixed_allowances' => $row[35],
                'emp_sal_insurance' => $row[36],
                'medical_insurance' => $row[37],
                'is_has_fixed_shift' => $row[38],
                'shifts_types_id' => $row[39],
                'is_has_finger' => $row[40],
                'vacation_formula' => $row[41],
                'sensitive_data' => $row[42],
                'branches_id' => $row[43],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            
        }
    }
}