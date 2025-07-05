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
            'Branch', 'Company', 'Added By', 'Updated By', 
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
            $this->getemp_military_statusText($employee->emp_military_status), 
            $employee->emp_qualification,
            $employee->qualification_year,
            $this->getqualification_gradeText($employee->qualification_grade),
            $employee->emp_start_date,
            $this->getfunctional_statusText($employee->functional_status),
            $this->getresignation_statusText($employee->resignation_status),
            $employee->resignation_date,
            $employee->resignation_cause,
            $this->getmtivation_typeText($employee->mtivation_type),
            $employee->mtivation,
            $this->getsal_cash_visaText($employee->sal_cash_visa),
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
            $this->getis_has_fixed_shiftText($employee->is_has_fixed_shift),
            $employee->shifts_type->type ?? 'غير محدد',
            $this->getis_has_fingerText($employee->is_has_finger),
            $this->getvacation_formulaText($employee->vacation_formula),
            $this->getsensitive_dataText($employee->sensitive_data),
            $employee->branches->branch_name ?? 'غير محدد',
            $employee->company->com_name ?? 'غير محدد'	,
            $employee->addedBy->name ?? 'غير محدد',
            $employee->updatedBy->name ?? 'غير محدد',
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

    private function getqualification_gradeText($qualification_gradeCode)
        {
            return match($qualification_gradeCode) {
                1 => 'امتياز',
                2 => 'جيد جدا',
                3=> 'جيد مرتفع',
                4=> 'جيد',
                5=> 'مقبول',
                default => 'غير محدد'
            };
        }

        private function getemp_military_statusText($emp_military_statusCode)
        {
            return match($emp_military_statusCode) {
                1 => 'ادى الخدمة',
                2 => 'اعفاء',
                3=> 'مؤجل',
                default => 'غير محدد'
            };
        }
        private function getfunctional_statusText($functional_statusCode)
        {
            return match($functional_statusCode) {
                1 => 'يعمل',
                2 => 'لايعمل',
                default => 'غير محدد'
            };
        }
        private function getresignation_statusText($resignation_statusCode)
        {
            return match($resignation_statusCode) {
                1 => 'استقالة',
                2 => 'فصل',
                3=> 'ترك العمل',
                4=> 'سن المعاش',
                5=> 'الوفاة',
                default => 'غير محدد'
            };
        }
        private function getmtivation_typeText($mtivation_typeCode)
        {
            return match($mtivation_typeCode) {
                1 => 'ثابت',
                2 => 'متغير',
                0=> 'لايوجد',
                default => 'غير محدد'
            };
        }
        private function getsal_cash_visaText($sal_cash_visaCode)
        {
            return match($sal_cash_visaCode) {
                1 => 'كاش',
                2 => 'فيزا',
                default => 'غير محدد'
            };
        }
        private function getis_has_fixed_shiftText($is_has_fixed_shiftCode)
        {
            return match($is_has_fixed_shiftCode) {
                1 => 'يوجد',
                2 => 'لايوجد',
                default => 'غير محدد'
            };
        }
        private function getis_has_fingerText($is_has_fingerCode)
        {
            return match($is_has_fingerCode) {
                1 => 'يوجد',
                2 => 'لايوجد',
                default => 'غير محدد'
            };
        }
        private function getvacation_formulaText($vacation_formulaCode)
        {
            return match($vacation_formulaCode) {
                1 => 'يوجد',
                2 => 'لايوجد',
                default => 'غير محدد'
            };
        }
        private function getsensitive_dataText($is_has_fingerCode)
        {
            return match($is_has_fingerCode) {
                1 => 'يوجد',
                2 => 'لايوجد',
                default => 'غير محدد'
            };
        }
    public function styles(Worksheet $sheet)
    {
        // تنسيق رؤوس الأعمدة
        $sheet->getStyle('A1:AU1')->applyFromArray([
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
        $sheet->getStyle('A1:AU' . ($sheet->getHighestRow()))
              ->getBorders()
              ->getAllBorders()
              ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // محاذاة النص لجميع الخلايا
        $sheet->getStyle('A2:AU' . ($sheet->getHighestRow()))
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
              ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

        return [];
    }
}