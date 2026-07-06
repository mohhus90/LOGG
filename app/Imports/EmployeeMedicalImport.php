<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

/**
 * Supports two CSV formats (auto-detected by column 0):
 *
 * A) Client CSV  — col 0 = numeric SR
 *    SR, Fake ID, HRID, English Name, Arabic Name, Position, Mobile,
 *    Reference Number, Relative, Address, NID, Gender, Date Of Birth, Age,
 *    Marital Status, Status, Hiring Date, Resignation Date, Hiring Documents,
 *    Military Certificate, Social Number, Start Date Of Social, Form 1 Comments,
 *    End Date Of Social, Form 6 Comments, Comments, Medical ID, [empty],
 *    Status(medical), Progress, Salary, Social Insurance salary
 *
 * B) System CSV  — col 0 = employee English name
 *    Name, namear, Mobile, emergency contact, emergency mobile, Email,
 *    National Id, Medical Id, Social Number, social status, Job, Salary,
 *    Date Of Birth, Gender, Marital Status, Military Status, Company,
 *    Education, Cv, Extra Comments, Username, Password, Status, Photo,
 *    Bank, Payment Channel, Card No, Vacation Type, hire date, hrid,
 *    serial number, Education Filed
 */
class EmployeeMedicalImport implements ToCollection, WithStartRow
{
    protected int $comCode;
    protected int $adminId;

    public int $updated  = 0;
    public int $skipped  = 0;
    public int $notFound = 0;
    public int $errors   = 0;
    public array $errorDetails = [];

    public function __construct(int $comCode, int $adminId)
    {
        $this->comCode = $comCode;
        $this->adminId = $adminId;
    }

    public function startRow(): int { return 2; }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $col0 = trim((string) ($row[0] ?? ''));
            // Client CSV: column 0 is a positive integer (SR number)
            if (is_numeric($col0) && (int)$col0 > 0) {
                $this->processClientRow($row);
            } else {
                $this->processSystemRow($row);
            }
        }
    }

    // ── Format A: Client CSV ────────────────────────────────────────────────

    private function processClientRow($row): void
    {
        $englishName = trim((string) ($row[3] ?? ''));
        $arabicName  = trim((string) ($row[4] ?? ''));
        if ($englishName === '' && $arabicName === '') {
            $this->skipped++;
            return;
        }

        $nid    = $this->nid($row[10] ?? null);
        $fakeId = $this->str($row[1]  ?? null);
        $hrid   = $this->str($row[2]  ?? null);

        if ($nid === null && $fakeId === null && $hrid === null) {
            $this->skipped++;
            return;
        }

        try {
            $employee = $this->findClientEmployee($nid, $fakeId, $hrid);
            if ($employee) {
                $data = $this->mapClientRow($row, $nid, $hrid);
                $clean = array_filter($data, fn($v) => $v !== null);
                try {
                    $employee->update($clean);
                } catch (\Illuminate\Database\QueryException $qe) {
                    // Retry without mobile if it caused a duplicate-key violation
                    if (str_contains($qe->getMessage(), '1062') && str_contains($qe->getMessage(), 'emp_mobile')) {
                        unset($clean['emp_mobile']);
                        $employee->update($clean);
                    } else {
                        throw $qe;
                    }
                }
                $this->updated++;
            } else {
                $this->notFound++;
            }
        } catch (\Exception $e) {
            $this->errors++;
            $this->errorDetails[] = "NID {$nid} / FakeID {$fakeId} / HRID {$hrid}: " . $e->getMessage();
        }
    }

    private function findClientEmployee(?string $nid, ?string $fakeId, ?string $hrid): ?Employee
    {
        if ($nid !== null) {
            $emp = Employee::where('com_code', $this->comCode)->where('national_id', $nid)->first();
            if ($emp) return $emp;
        }
        if ($fakeId !== null) {
            $emp = Employee::where('com_code', $this->comCode)->where('employee_id', $fakeId)->first();
            if ($emp) return $emp;
        }
        if ($hrid !== null) {
            $emp = Employee::where('com_code', $this->comCode)->where('hrid', $hrid)->first();
            if ($emp) return $emp;
        }
        return null;
    }

    private function mapClientRow($row, ?string $nid, ?string $hrid): array
    {
        $statusRaw = strtolower(trim((string) ($row[15] ?? '')));
        $functional_status = match($statusRaw) {
            'active'                 => 1,
            'resigned', 'terminated' => 2,
            default                  => null,
        };
        $resignation_status = null;
        if ($statusRaw === 'terminated') $resignation_status = 2;
        elseif ($statusRaw === 'resigned') $resignation_status = 1;

        $insuranceNo = $this->insuranceNo($row[20] ?? null);

        // Skip updating salary if it is 0 (resigned marker) for safety
        $salary = $this->num($row[30] ?? null);
        if ($salary !== null && $salary <= 0) $salary = null;

        $salInsurance = $this->num($row[31] ?? null);
        if ($salInsurance !== null && $salInsurance <= 0) $salInsurance = null;

        return [
            'national_id'             => $nid,
            'hrid'                    => $hrid,
            'emp_mobile'              => $this->clientPhone($row[6]  ?? null),
            'relative_relation'       => $this->str($row[8]  ?? null),
            'employee_address'        => $this->str($row[9]  ?? null),
            'emp_gender'              => $this->genderText($row[11] ?? null),
            'birth_date'              => $this->date($row[12] ?? null),
            'emp_social_status'       => $this->maritalText($row[14] ?? null),
            'functional_status'       => $functional_status,
            'resignation_status'      => $resignation_status,
            'emp_start_date'          => $this->date($row[16] ?? null),
            'resignation_date'        => $this->date($row[17] ?? null),
            'hiring_documents_status' => $this->str($row[18] ?? null),
            'emp_military_status'     => $this->militaryText($row[19] ?? null),
            'insurance_no'            => $insuranceNo,
            'insurance_status'        => $insuranceNo !== null ? 1 : null,
            'insurance_start_date'    => $this->date($row[21] ?? null),
            'form1_notes'             => $this->str($row[22] ?? null),
            'insurance_end_date'      => $this->date($row[23] ?? null),
            'form6_notes'             => $this->str($row[24] ?? null),
            'client_notes'            => $this->str($row[25] ?? null),
            'medical_id'              => $this->medicalId($row[26] ?? null),
            'medical_status'          => $this->medicalStr($row[28] ?? null),
            'medical_progress'        => $this->medicalStr($row[29] ?? null),
            'emp_sal'                 => $salary,
            'emp_sal_insurance'       => $salInsurance,
        ];
    }

    // ── Format B: System CSV ────────────────────────────────────────────────

    private function processSystemRow($row): void
    {
        $nid     = $this->str($row[6]  ?? null);
        $empCode = $this->str($row[20] ?? null);

        if ($nid === null && $empCode === null) {
            $this->skipped++;
            return;
        }

        try {
            $employee = $this->findSystemEmployee($nid, $empCode);
            if ($employee) {
                $data = $this->mapSystemRow($row);
                $employee->update($data);
                $this->updated++;
            } else {
                $this->notFound++;
            }
        } catch (\Exception $e) {
            $this->errors++;
            $this->errorDetails[] = "NID {$nid} / ID {$empCode}: " . $e->getMessage();
        }
    }

    private function findSystemEmployee(?string $nid, ?string $empCode): ?Employee
    {
        if ($nid !== null) {
            $emp = Employee::where('com_code', $this->comCode)->where('national_id', $nid)->first();
            if ($emp) return $emp;
        }
        if ($empCode !== null) {
            return Employee::where('com_code', $this->comCode)->where('employee_id', $empCode)->first();
        }
        return null;
    }

    private function mapSystemRow($row): array
    {
        $data = [
            'employee_name_E'     => $this->str($row[0]  ?? null),
            'employee_name_A'     => $this->str($row[1]  ?? null),
            'emp_mobile'          => $this->phone($row[2]  ?? null),
            'relative_relation'   => $this->str($row[3]  ?? null),
            'reference_mobile'    => $this->phone($row[4]  ?? null),
            'emp_email'           => $this->str($row[5]  ?? null),
            'national_id'         => $this->str($row[6]  ?? null),
            'medical_id'          => $this->str($row[7]  ?? null),
            'insurance_no'        => $this->str($row[8]  ?? null),
            'insurance_status'    => $this->insuranceStatus($row[9]  ?? null),
            'emp_sal'             => $this->num($row[11] ?? null),
            'birth_date'          => $this->date($row[12] ?? null),
            'emp_gender'          => $this->gender($row[13] ?? null),
            'emp_social_status'   => $this->marital($row[14] ?? null),
            'emp_military_status' => $this->military($row[15] ?? null),
            'emp_qualification'   => $this->str($row[17] ?? null),
            'client_notes'        => $this->str($row[19] ?? null),
            'functional_status'   => $this->funcStatus($row[22] ?? null),
            'bank_name'           => $this->str($row[24] ?? null),
            'sal_cash_visa'       => $this->payment($row[25] ?? null),
            'bank_account'        => $this->str($row[26] ?? null),
            'emp_start_date'      => $this->date($row[28] ?? null),
        ];
        return array_filter($data, fn($v) => $v !== null);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function nid($val): ?string
    {
        $s = trim((string) ($val ?? ''));
        if ($s === '') return null;
        // Skip Excel scientific notation (large numbers exported as 2.9E+13)
        if (preg_match('/^[\d.]+[Ee][+\-]?\d+$/', $s)) return null;
        if (preg_match('/^=""(.+)""$/', $s, $m)) $s = $m[1];
        elseif (preg_match('/^="(.+)"$/', $s, $m)) $s = $m[1];
        $digits = preg_replace('/\D/', '', $s);
        return strlen($digits) >= 10 ? $digits : null;
    }

    private function insuranceNo($val): ?string
    {
        $s = $this->str($val);
        if ($s === null) return null;
        $lower = strtolower($s);
        if (str_starts_with($lower, 'not applicable') || in_array($lower, ['n/a', 'na', '-', '0'])) return null;
        $digits = preg_replace('/\D/', '', $s);
        return strlen($digits) >= 4 ? $digits : null;
    }

    private function medicalId($val): ?string
    {
        $s = $this->str($val);
        if ($s === null) return null;
        if (str_starts_with(strtolower($s), 'not applicable') || strtolower($s) === 'n/a') return null;
        return $s;
    }

    private function medicalStr($val): ?string
    {
        $s = $this->str($val);
        if ($s === null) return null;
        if (str_starts_with(strtolower($s), 'not applicable')) return null;
        return $s;
    }

    private function clientPhone($val): ?string
    {
        $s = trim((string) ($val ?? ''));
        if ($s === '') return null;
        // Split on "/" to separate multiple numbers, keep only the first
        $first = preg_split('#\s*/\s*#', $s)[0];
        // Strip trailing labels like "Whats", "WhatsApp", "البريد", etc.
        $first = preg_replace('/\s*(Whats\w*|البريد)\s*.*/ui', '', $first);
        // Remove all non-digit characters (spaces, dashes, parentheses …)
        $digits = preg_replace('/\D/', '', $first);
        if ($digits === '') return null;
        // Normalize 10-digit number starting with 1  →  prepend 0
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) $digits = '0' . $digits;
        // Accept only valid Egyptian mobile: 11 digits starting with 01
        if (strlen($digits) !== 11 || !str_starts_with($digits, '01')) return null;
        return $digits;
    }

    private function genderText($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'male'   => 1,
            'female' => 2,
            default  => null,
        };
    }

    private function maritalText($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'single'   => 1,
            'married'  => 2,
            'widowed'  => 3,
            'divorced' => 4,
            default    => null,
        };
    }

    private function militaryText($val): ?int
    {
        $s = strtolower(trim((string) ($val ?? '')));
        if (str_contains($s, 'serve completed')) return 1;
        if (str_contains($s, 'temporary exempted')) return 4;
        if (str_contains($s, 'exempted')) return 2;
        if (str_contains($s, 'postponed')) return 3;
        if (str_contains($s, 'not required')) return 5;
        return null;
    }

    private function str($val): ?string
    {
        $s = trim((string) ($val ?? ''));
        if (preg_match('/^=""(.+)""$/', $s, $m)) $s = $m[1];
        elseif (preg_match('/^="(.+)"$/', $s, $m)) $s = $m[1];
        return $s !== '' ? $s : null;
    }

    private function phone($val): ?string
    {
        $s = trim((string) ($val ?? ''));
        if ($s === '') return null;
        $first  = preg_split('/[\/,]/', $s)[0];
        $digits = preg_replace('/\D/', '', trim($first));
        if ($digits === '') return null;
        if (!str_starts_with($digits, '0')) $digits = '0' . $digits;
        return $digits;
    }

    private function num($val): ?float
    {
        if ($val === null || $val === '') return null;
        $clean = str_replace(',', '', trim((string) $val));
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function insuranceStatus($val): ?int
    {
        $s = strtolower(trim((string) ($val ?? '')));
        if ($s === 'yes') return 1;
        if ($s === '')    return null;
        return 2;
    }

    private function gender($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'male'   => 1,
            'female' => 2,
            default  => null,
        };
    }

    private function marital($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'single'   => 1,
            'married'  => 2,
            'widowed'  => 3,
            'divorced' => 4,
            default    => null,
        };
    }

    private function military($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'completed'    => 1,
            'exempted'     => 2,
            'postponed'    => 3,
            'not required' => 5,
            default        => null,
        };
    }

    private function funcStatus($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'hired'    => 1,
            'resigned' => 2,
            default    => null,
        };
    }

    private function payment($val): ?int
    {
        return match(strtolower(trim((string) ($val ?? '')))) {
            'cash' => 1,
            'bank' => 2,
            default => null,
        };
    }

    private function date($val): ?string
    {
        if (empty($val)) return null;
        $s = trim((string) $val);
        if ($s === '') return null;

        if (preg_match('/^=""(.+)""$/', $s, $m)) $s = $m[1];
        elseif (preg_match('/^="(.+)"$/', $s, $m)) $s = $m[1];

        // Reject non-date strings
        if (!preg_match('/\d/', $s)) return null;
        $lower = strtolower($s);
        if (in_array($lower, ['n/a', 'in process', 'in progress', '-'])) return null;
        if (preg_match('/^(code\s*\d|check\s|in\s+process|in\s+progress)/i', $s)) return null;

        // Excel serial number
        if (is_numeric($s) && (float)$s > 10000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$s)->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        // Arabic month names
        static $arabicMonths = [
            'يناير'  => '01', 'فبراير' => '02', 'مارس'   => '03', 'أبريل'  => '04',
            'مايو'   => '05', 'يونيو'  => '06', 'يوليو'  => '07', 'أغسطس' => '08',
            'سبتمبر' => '09', 'أكتوبر' => '10', 'نوفمبر' => '11', 'ديسمبر' => '12',
        ];
        foreach ($arabicMonths as $ar => $num) {
            if (str_contains($s, $ar)) {
                $parts = explode('-', $s);
                if (count($parts) === 3) {
                    $day  = str_pad(trim($parts[0]), 2, '0', STR_PAD_LEFT);
                    $yr   = (int) trim($parts[2]);
                    $year = $yr >= 30 ? 1900 + $yr : 2000 + $yr;
                    return sprintf('%04d-%s-%s', $year, $num, $day);
                }
                break;
            }
        }

        // DD-Mon-YY or D/Mon/YYYY  e.g. "22-Jun-96", "9/Mar/2021"
        static $months3 = [
            'jan'=>'01','feb'=>'02','mar'=>'03','apr'=>'04','may'=>'05','jun'=>'06',
            'jul'=>'07','aug'=>'08','sep'=>'09','oct'=>'10','nov'=>'11','dec'=>'12',
        ];
        if (preg_match('#^(\d{1,2})[-/]([A-Za-z]{3})[-/](\d{2,4})$#', $s, $m)) {
            $mon = $months3[strtolower($m[2])] ?? null;
            if ($mon) {
                $yr   = (int)$m[3];
                $year = $yr < 100 ? ($yr >= 30 ? 1900 + $yr : 2000 + $yr) : $yr;
                return sprintf('%04d-%s-%02d', $year, $mon, (int)$m[1]);
            }
        }

        // D/M/YYYY or DD/MM/YYYY  e.g. "26/1/2022", "15/11/2021"
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $s, $m)) {
            return sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
        }

        // YYYY-MM-DD already valid
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;

        // Carbon fallback
        try {
            $date = \Carbon\Carbon::parse($s);
            if ($date->year > now()->year + 5) $date->subYears(100);
            return $date->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
