<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeeImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            Employee::create([
                'added_by' => auth()->guard('admin')->user()->id,
                'com_code' => auth()->guard('admin')->user()->com_code,
                'employee_id' => $row[0],
                'finger_id' => $row[1],
                'employee_name_A' => $row[2],
                'employee_name_E' => $row[3],
                'employee_address' => $row[4],
                'emp_gender' => is_numeric($row[5]) ? $row[5] : null,
                'emp_social_status' => is_numeric($row[6]) ? $row[6] : null,
                'emp_military_status' => is_numeric($row[7]) ? $row[7] : null,
                'emp_qualification' => $row[8],
                'qualification_year' => $row[9],
                'qualification_grade' => $row[10],
                'emp_start_date' => !empty($row[11]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[11]) : null,
                'functional_status' => is_numeric($row[12]) ? $row[12] : null,
                'insurance_status'  => is_numeric($row[13]) ? $row[13] : null,
                'resignation_status' => is_numeric($row[14]) ? $row[14] : null,
                'resignation_date' => !empty($row[15]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[15]) : null,
                'resignation_cause' => $row[16],
                'motivation_type' => $row[17],
                'motivation' => $row[18],
                'sal_cash_visa' => $row[19],
                'bank_name' => $row[20],
                'bank_account' => $row[21],
                'bank_ID' => $row[22],
                'bank_branch' => $row[23],
                'daily_work_hours' => $row[24],
                'emp_jobs_id' => is_numeric($row[25]) ? $row[25] : null,
                'national_id' => $row[26],
                'insurance_no' => $row[27],
                'emp_departments_id' => is_numeric($row[28]) ? $row[28] : null,
                'emp_home_tel' => $row[29],
                'emp_mobile' => $row[30],
                'emp_email' => $row[31],
                'emp_photo' => $row[32],
                'birth_date' => $row[33],
                'emp_sal' => $row[34],
                'emp_fixed_allowances' => $row[35],
                'emp_sal_insurance' => $row[36],
                'medical_insurance' => $row[37],
                'is_has_fixed_shift' => $row[38],
                'shifts_types_id' => $row[39],
                'is_has_finger' => $row[40],
                'vacation_formula' => $row[41],
                'sensitive_data' => $row[42],
                'branches_id' => is_numeric($row[43]) ? $row[43] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
