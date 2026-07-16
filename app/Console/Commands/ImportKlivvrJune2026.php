<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Jobs_categories;
use App\Models\PayrollFactor;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// أمر مؤقت لمرة واحدة (pilot) لاستيراد موظفي وعوامل رواتب Klivvr ليونيو 2026
// مباشرة من ملف "Payroll June Klivvr 2026.xlsx" الأصلي، بهدف التحقق من مطابقة
// محرك الحساب فى NEXA لنفس الأرقام النهائية الموجودة فى الإكسيل قبل بناء شاشة
// الاستيراد العامة القابلة لإعادة الاستخدام لبقية العملاء.
class ImportKlivvrJune2026 extends Command
{
    protected $signature = 'payroll:import-klivvr-june2026 {--file= : مسار ملف CSV المُستخرج من الإكسيل}';
    protected $description = 'استيراد موظفي وعوامل رواتب Klivvr ليونيو 2026 (pilot تحقق من المطابقة)';

    public function handle(): int
    {
        $file = $this->option('file')
            ?? 'C:/Users/MOHAME~1.ELH/AppData/Local/Temp/claude/d--xampp-htdocs-NEXA/e27e7eb6-2dfc-4644-b8cc-f776a4452569/scratchpad/klivvr_june2026.csv';

        if (!file_exists($file)) {
            $this->error("الملف غير موجود: {$file}");
            return self::FAILURE;
        }

        $client = Client::firstOrCreate(
            ['com_code' => 1, 'client_name' => 'Klivvr'],
            ['client_name_A' => 'كليفر', 'active' => 1]
        );

        $defaultShift = DB::table('shifts_types')->where('com_code', 1)->first();
        if (!$defaultShift) {
            $this->error('يجب إضافة نوع وردية أولاً قبل الاستيراد');
            return self::FAILURE;
        }

        $adminId = DB::table('admins')->where('com_code', 1)->value('id');

        $defaultJob = Jobs_categories::firstOrCreate(
            ['job_name' => 'Klivvr Staff', 'com_code' => 1],
            ['added_by' => $adminId]
        );

        $csv = fopen($file, 'r');
        $header = fgetcsv($csv);
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $idx = array_flip($header);
        $get = fn(array $row, string $key) => trim((string) ($row[$idx[$key]] ?? ''));
        $num = fn(array $row, string $key) => is_numeric($v = trim((string) ($row[$idx[$key]] ?? ''))) ? (float) $v : 0.0;

        $employees = 0;
        $factors = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($csv)) !== false) {
                $code = $get($row, 'code');
                if ($code === '') continue;

                $name = $get($row, 'name');
                $hireDate = null;
                if ($get($row, 'hire_date') !== '') {
                    try { $hireDate = Carbon::parse($get($row, 'hire_date'))->format('Y-m-d'); } catch (\Exception) {}
                }

                // طابق أولاً على hrid ضمن نفس العميل (قد يكون الموظف موجودًا مسبقًا
                // برقم employee_id مختلف عن كود العميل)، ثم على employee_id كحل احتياطي،
                // فقط أنشئ سجلاً جديدًا لو لم يوجد أي تطابق — لتفادي تكرار موظفين حقيقيين
                $employee = Employee::where('client_id', $client->id)
                    ->where(fn($q) => $q->where('hrid', $code)->orWhere('employee_id', $code))
                    ->first();

                $fields = [
                    'employee_name_E'   => $name,
                    'employee_name_A'   => $name,
                    'client_id'         => $client->id,
                    'hrid'              => $code,
                    'bank_name'         => $get($row, 'bank') ?: null,
                    'emp_start_date'    => $hireDate,
                    'functional_status' => 1,
                    'emp_sal'           => $num($row, 'fixed_salary'),
                    'emp_sal_insurance' => $num($row, 'si_fixed'),
                    'apply_income_tax'  => true,
                ];

                if ($employee) {
                    $employee->update($fields);
                } else {
                    $employee = Employee::create(array_merge($fields, [
                        'employee_id'     => $code,
                        'emp_jobs_id'     => $defaultJob->id,
                        'finger_id'       => null,
                        'shifts_types_id' => $defaultShift->id,
                        'is_has_finger'   => 2,
                        'branches_id'     => null,
                        'com_code'        => 1,
                        'added_by'        => $adminId,
                    ]));
                }
                $employees++;

                PayrollFactor::updateOrCreate(
                    ['employee_id' => $employee->id, 'month' => 6, 'year' => 2026],
                    [
                        'client_id'          => $client->id,
                        'working_days'       => $num($row, 'working_days'),
                        'overtime_hours'     => $num($row, 'overtime_hours'),
                        'absence_hours'      => $num($row, 'absence_hours'),
                        'leave_days'         => $num($row, 'leave_days'),
                        'no_show_days'       => $num($row, 'no_show_days'),
                        'unpaid_leave_days'  => $num($row, 'unpaid_leave_days'),
                        'sick_leave_days'    => $num($row, 'sick_leave_days'),
                        'sick_leave_balance' => $num($row, 'sick_leave_balance'),
                        'penalty_days'       => $num($row, 'penalty_days'),
                        'settlement_hours'   => $num($row, 'settlement_hours'),
                        'settlement_days'    => $num($row, 'settlement_days'),
                        'settlement_amount'  => $num($row, 'settlement_amount'),
                        'bonus_amount'       => $num($row, 'bonus'),
                        'other_allowance'    => round($num($row, 'allowances_k') + $num($row, 'other_s') + $num($row, 'leavers_t'), 2),
                        'other_deduction'    => $num($row, 'penalties'),
                        'monthly_stamp_tax'  => 0,
                        'is_held'            => false,
                        'com_code'           => 1,
                        'added_by'           => $adminId,
                    ]
                );
                $factors++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('خطأ: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("تم استيراد {$employees} موظف و {$factors} سجل عوامل رواتب لـ Klivvr - يونيو 2026");
        return self::SUCCESS;
    }
}
