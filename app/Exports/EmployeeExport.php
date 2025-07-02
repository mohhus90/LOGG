<?php
namespace App\Exports;

use App\Models\Employee;
use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithMapping
{
    public function collection()
    {
        return Employee::with('department')->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Employee ID', 'Finger ID', 'Employee Name', 'Employee Address', 
            'Gender', 'Social Status', 'Military Status', 'Qualification', 
            'Qualification Year', 'Qualification Grade', 'Start Date', 
            'Functional Status', 'Resignation Status', 'Resignation Date', 
            'Resignation Cause', 'Motivation Type', 'Motivation', 'Salary Cash Visa', 
            'Bank Name', 'Bank Account', 'Bank ID', 'Bank Branch', 'Daily Work Hours', 
            'job name', 'National ID', 'Department', 'Home Tel', 'Mobile', 
            'Email', 'Photo', 'Birth Date', 'Salary', 'Fixed Allowances', 
            'Salary Insurance', 'Medical Insurance', 'Has Fixed Shift', 
            'Shift Type', 'Has Finger', 'Vacation Formula', 'Sensitive Data', 
            'Branch ID', 'Company Code', 'Added By', 'Updated By', 
            'Created At', 'Updated At'
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->employee_id,
            $employee->finger_id,
            $employee->employee_name,
            $employee->employee_address,
            $this->getGenderText($employee->emp_gender),
            $this->getemp_social_statusText($employee->emp_social_status),
            $employee->emp_military_status,
            $employee->emp_qualification,
            $employee->qualification_year,
            $employee->qualification_grade,
            $employee->emp_start_date,
            $employee->functional_status,
            $employee->resignation_status,
            $employee->resignation_date,
            $employee->resignation_cause,
            $employee->mtivation_type,
            $employee->mtivation,
            $employee->sal_cash_visa,
            $employee->bank_name,
            $employee->bank_account,
            $employee->bank_ID,
            $employee->bank_branch,
            $employee->daily_work_hours,
            $employee->jobs_categories->job_name ?? 'غير محدد',
            $employee->national_id,
            $employee->department->dep_name ?? 'غير محدد', // عرض اسم القسم بدلاً من ID
            $employee->emp_home_tel,
            $employee->emp_mobile,
            $employee->emp_email,
            $employee->emp_photo,
            $employee->birth_date,
            $employee->emp_sal,
            $employee->emp_fixed_allowances,
            $employee->emp_sal_insurance,
            $employee->medical_insurance,
            $employee->is_has_fixed_shift,
            $employee->shifts_type->type ?? 'غير محدد',
            $employee->is_has_finger,
            $employee->vacation_formula,
            $employee->sensitive_data,
            $employee->branches_id,
            $employee->com_code,
            $employee->added_by,
            $employee->updated_by,
            $employee->created_at,
            $employee->updated_at
        ];
    }

    private function getGenderText($genderCode)
    {
        return match($genderCode) {
            1 => 'ذكر',
            2 => 'أنثى',
            default => 'غير محدد'
        };
    }
    
    private function getemp_social_statusText($emp_social_statusCode)
    {
        return match($emp_social_statusCode) {
            1 => 'اعزب',
            2 => 'متزوج',
            3=> 'متزوج ويعول',
            default => 'غير محدد'
        };
    }

    public function styles(Worksheet $sheet)
    {
        // تنسيق رؤوس الأعمدة
        $sheet->getStyle('A1:AT1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D3D3D3']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ]
        ]);

        // تنسيق الحدود لجميع الخلايا
        $sheet->getStyle('A1:AT' . ($sheet->getHighestRow()))
              ->getBorders()
              ->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // محاذاة النص لجميع الخلايا
        $sheet->getStyle('A2:AT' . ($sheet->getHighestRow()))
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        return [];
    }
}