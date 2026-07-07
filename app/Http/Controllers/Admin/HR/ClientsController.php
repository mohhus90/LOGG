<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Jobs_categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ClientsController extends Controller
{
    public function index()
    {
        $com_code = auth()->guard('admin')->user()->com_code;
        $data = Client::where('com_code', $com_code)->orderBy('id', 'ASC')->get();
        return view('admin.clients.index', compact('data'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
        ], [
            'client_name.required' => 'يجب إدخال اسم العميل',
        ]);

        DB::beginTransaction();
        try {
            $com_code = auth()->guard('admin')->user()->com_code;
            $exists = Client::where(['com_code' => $com_code, 'client_name' => $request->client_name])->exists();
            if ($exists) {
                return redirect()->back()->with(['error' => 'اسم العميل مسجل من قبل'])->withInput();
            }
            Client::create([
                'client_name'    => $request->client_name,
                'client_name_A'  => $request->client_name_A,
                'contact_person' => $request->contact_person,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'address'        => $request->address,
                'industry'       => $request->industry,
                'active'         => $request->active ?? 1,
                'notes'          => $request->notes,
                'com_code'       => $com_code,
                'added_by'       => auth()->guard('admin')->user()->id,
            ]);
            DB::commit();
            return redirect()->route('clients.index')->with(['success' => 'تم إضافة العميل بنجاح']);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Clients store error: ' . $ex->getMessage());
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ: ' . $ex->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $data = Client::findOrFail($id);
        return view('admin.clients.update', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'client_name' => ['required', Rule::unique('clients')->ignore($id)],
        ], [
            'client_name.required' => 'يجب إدخال اسم العميل',
            'client_name.unique'   => 'اسم العميل مسجل من قبل',
        ]);

        DB::beginTransaction();
        try {
            Client::where('id', $id)->update([
                'client_name'    => $request->client_name,
                'client_name_A'  => $request->client_name_A,
                'contact_person' => $request->contact_person,
                'phone'          => $request->phone,
                'email'          => $request->email,
                'address'        => $request->address,
                'industry'       => $request->industry,
                'active'         => $request->active ?? 1,
                'notes'          => $request->notes,
                'updated_by'     => auth()->guard('admin')->user()->id,
            ]);
            DB::commit();
            return redirect()->route('clients.index')->with(['success' => 'تم تحديث بيانات العميل بنجاح']);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Clients update error: ' . $ex->getMessage());
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ: ' . $ex->getMessage()])->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $empCount = Employee::where('client_id', $id)->count();
            if ($empCount > 0) {
                return redirect()->back()->with(['error' => 'لا يمكن حذف العميل لأنه يوجد ' . $empCount . ' موظف مرتبط به']);
            }
            Client::where('id', $id)->delete();
            return redirect()->route('clients.index')->with(['success' => 'تم حذف العميل بنجاح']);
        } catch (\Exception $ex) {
            return redirect()->back()->with(['error' => 'حدث خطأ: ' . $ex->getMessage()]);
        }
    }

    // ── CSV Import ──────────────────────────────────────────────

    public function importForm($id)
    {
        $client = Client::findOrFail($id);
        return view('admin.clients.import', compact('client'));
    }

    public function importCsv(Request $request, $id)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:xlsx,xls,csv,txt',
        ], [
            'csv_file.required' => 'يجب رفع ملف Excel',
            'csv_file.mimes'    => 'يجب أن يكون الملف بصيغة Excel أو CSV',
        ]);

        $client   = Client::findOrFail($id);
        $com_code = auth()->guard('admin')->user()->com_code;
        $admin_id = auth()->guard('admin')->user()->id;

        $defaultShift = DB::table('shifts_types')->where('com_code', $com_code)->first();
        if (!$defaultShift) {
            return redirect()->back()->with(['error' => 'يجب إضافة نوع وردية أولاً قبل استيراد الموظفين']);
        }

        $file        = $request->file('csv_file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $worksheet   = $spreadsheet->getActiveSheet();
        // toArray: nullValue, calculateFormulas, formatData, returnCellRef
        $allRows     = $worksheet->toArray(null, false, false, false);

        // Remove header row (row 0)
        array_shift($allRows);

        $imported = 0;
        $updated  = 0;
        $errors   = [];
        $rowNum   = 1;

        foreach ($allRows as $row) {
            $rowNum++;
            if (count($row) < 5) continue;

            // PhpSpreadsheet returns UTF-8 strings natively — no encoding conversion needed
            $col = fn(int $i): string => isset($row[$i]) ? trim((string) ($row[$i] ?? '')) : '';

            $englishName = $this->cleanStr($col(3));
            $arabicName  = $this->cleanStr($col(4));
            if ($englishName === '' && $arabicName === '') continue;

            // ── NID: handle scientific notation and Excel formula wrappers ──
            $nid = $col(10);
            if (preg_match('/^=""(.+)""$/', $nid, $m)) $nid = $m[1];
            elseif (preg_match('/^="(.+)"$/', $nid, $m)) $nid = $m[1];
            if (preg_match('/^[\d.]+[Ee][+\-]?\d+$/', $nid)) {
                $nid = sprintf('%.0f', (float) $nid);
            }
            $nid = preg_replace('/\D/', '', $nid);
            if (strlen($nid) < 10) $nid = '';

            $fakeId = $col(1);
            $hrid   = $col(2);

            // ── Find existing employee: NID → Fake ID → HRID ──
            $existingEmployee = null;
            if ($nid !== '') {
                $existingEmployee = Employee::where('com_code', $com_code)->where('national_id', $nid)->first();
            }
            if (!$existingEmployee && $fakeId !== '') {
                $existingEmployee = Employee::where('employee_id', $fakeId)->first();
            }
            if (!$existingEmployee && $hrid !== '') {
                $existingEmployee = Employee::where('hrid', $hrid)->where('client_id', $client->id)->first();
            }

            // ── Functional status (col 15, NOT col 28 which is medical status) ──
            $statusRaw = strtolower($col(15)) ?: 'active';
            $functional_status  = 1;
            $resignation_status = null;
            if (in_array($statusRaw, ['resigned', 'terminated'])) {
                $functional_status  = 2;
                $resignation_status = ($statusRaw === 'terminated') ? 2 : 1;
            }

            // ── Gender (col 11) ──
            $emp_gender = strtolower($col(11)) === 'female' ? 2 : 1;

            // ── Marital status (col 14) ──
            $maritalRaw = strtolower($col(14));
            $emp_social_status = 1;
            if ($maritalRaw === 'married')  $emp_social_status = 2;
            elseif ($maritalRaw === 'widowed')  $emp_social_status = 3;
            elseif ($maritalRaw === 'divorced')  $emp_social_status = 4;

            // ── Military certificate (col 19) — check "temporary exempted" before "exempted" ──
            $militaryRaw = strtolower($col(19));
            $emp_military_status = null;
            if ($emp_gender !== 2) {
                if (str_contains($militaryRaw, 'serve completed'))     $emp_military_status = 1;
                elseif (str_contains($militaryRaw, 'temporary exempted')) $emp_military_status = 4;
                elseif (str_contains($militaryRaw, 'exempted'))           $emp_military_status = 2;
                elseif (str_contains($militaryRaw, 'postponed'))          $emp_military_status = 3;
                elseif (str_contains($militaryRaw, 'not required'))       $emp_military_status = 5;
            }

            // ── Dates ──
            $emp_start_date   = $this->parseDate($col(16));
            $resignation_date = $this->parseDate($col(17));
            $birth_date       = $this->parseDate($col(12));
            $insurance_start  = $this->parseDate($col(21));
            $insurance_end    = $this->parseDate($col(23));

            // ── Job category (col 5) ──
            $positionName = $this->cleanStr($col(5)) ?: 'General';
            $job = Jobs_categories::firstOrCreate(
                ['job_name' => $positionName, 'com_code' => $com_code],
                ['added_by' => $admin_id]
            );

            // ── Mobile (col 6): validate Egyptian format (11 digits, starts with 01) ──
            $mobile = $this->parseMobile($col(6), $com_code, $existingEmployee?->id);

            // ── Insurance number (col 20) ──
            $insuranceNo = $col(20);
            if ($insuranceNo === '' || !is_numeric($insuranceNo)
                || in_array(strtolower($insuranceNo), ['n/a', '-', '0'])) {
                $insuranceNo = null;
            }
            if ($insuranceNo !== null) {
                $insQuery = Employee::where('insurance_no', $insuranceNo)->where('com_code', $com_code);
                if ($existingEmployee) $insQuery->where('id', '!=', $existingEmployee->id);
                if ($insQuery->exists()) $insuranceNo = null;
            }

            // ── Medical ID (col 26): filter "Not Applicable*", "N/A" ──
            $medicalId = $col(26);
            if ($medicalId === '' || preg_match('/^not\s*applicable/i', $medicalId)
                || in_array(strtolower($medicalId), ['n/a', '-'])) {
                $medicalId = null;
            }

            // ── Medical status (col 28): filter "Not Applicable*" ──
            $medicalStatus = $col(28);
            if ($medicalStatus === '' || preg_match('/^not\s*applicable/i', $medicalStatus)) {
                $medicalStatus = null;
            }

            // ── Medical progress (col 29): filter "Not Applicable*" ──
            $medicalProgress = $col(29);
            if ($medicalProgress === '' || preg_match('/^not\s*applicable/i', $medicalProgress)) {
                $medicalProgress = null;
            }

            // ── Salary (col 30): skip 0 to avoid zeroing valid salaries ──
            $salaryRaw = preg_replace('/[^\d.]/', '', $col(30));
            $salary = ($salaryRaw !== '' && (float) $salaryRaw > 0) ? (float) $salaryRaw : null;

            // ── Social insurance salary (col 31) ──
            $salInsRaw = preg_replace('/[^\d.]/', '', $col(31));
            $salInsurance = ($salInsRaw !== '' && (float) $salInsRaw > 0) ? (float) $salInsRaw : null;

            // ── Fields shared between insert and update ──
            $fields = [
                'employee_name_E'         => $englishName ?: '-',
                'employee_name_A'         => $arabicName ?: $englishName ?: '-',
                'employee_address'        => $col(9) ?: null,
                'emp_gender'              => $emp_gender,
                'emp_social_status'       => $emp_social_status,
                'emp_military_status'     => $emp_military_status,
                'emp_start_date'          => $emp_start_date,
                'functional_status'       => $functional_status,
                'resignation_status'      => $resignation_status,
                'resignation_date'        => $resignation_date,
                'birth_date'              => $birth_date,
                'national_id'             => $nid !== '' ? $nid : null,
                'insurance_no'            => $insuranceNo,
                'insurance_status'        => $insuranceNo !== null ? 1 : null,
                'insurance_start_date'    => $insurance_start,
                'insurance_end_date'      => $insurance_end,
                'emp_mobile'              => $mobile,
                'emp_jobs_id'             => $job->id,
                'client_id'               => $client->id,
                'hrid'                    => $hrid ?: null,
                'reference_mobile'        => $col(7) ?: null,
                'relative_relation'       => $col(8) ?: null,
                'hiring_documents_status' => $col(18) ?: null,
                'form1_notes'             => $col(22) ?: null,
                'form6_notes'             => $col(24) ?: null,
                'client_notes'            => $col(25) ?: null,
                'medical_id'              => $medicalId,
                'medical_status'          => $medicalStatus,
                'medical_progress'        => $medicalProgress,
                'emp_sal'                 => $salary,
                'emp_sal_insurance'       => $salInsurance,
            ];

            try {
                if ($existingEmployee) {
                    if ($fakeId !== '' && $existingEmployee->employee_id !== $fakeId) {
                        $idTaken = Employee::where('employee_id', $fakeId)
                            ->where('id', '!=', $existingEmployee->id)->exists();
                        if (!$idTaken) $fields['employee_id'] = $fakeId;
                    }
                    $fields['updated_by'] = $admin_id;
                    $existingEmployee->update($fields);
                    $updated++;
                } else {
                    $employee_id = $fakeId !== '' ? $fakeId
                        : 'EMP-' . str_pad((Employee::where('com_code', $com_code)->max('id') ?? 0) + 1, 5, '0', STR_PAD_LEFT);
                    Employee::create(array_merge($fields, [
                        'employee_id'     => $employee_id,
                        'finger_id'       => null,
                        'shifts_types_id' => $defaultShift->id,
                        'is_has_finger'   => 2,
                        'branches_id'     => null,
                        'com_code'        => $com_code,
                        'added_by'        => $admin_id,
                    ]));
                    $imported++;
                }
            } catch (\Exception $ex) {
                $errors[] = "صف $rowNum ({$englishName}): " . $ex->getMessage();
                Log::error("CSV import row $rowNum error: " . $ex->getMessage());
            }
        }

        $message = "تم إضافة {$imported} موظف جديد. تم تحديث {$updated} موظف موجود.";
        if (!empty($errors)) {
            $message .= ' أخطاء: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $message .= ' ... و ' . (count($errors) - 5) . ' أخطاء أخرى';
        }

        return redirect()->route('clients.index')->with(['success' => $message]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function cleanStr(string $raw): string
    {
        // Remove non-breaking spaces (\xA0 in Latin-1, \xC2\xA0 in UTF-8) and other
        // problematic bytes that MySQL utf8/utf8mb4 rejects when the CSV is Latin-1 encoded
        $clean = str_replace(["\xC2\xA0", "\xA0", "\xEF\xBB\xBF"], [' ', ' ', ''], $raw);
        return trim(preg_replace('/\s+/', ' ', $clean));
    }

    private function parseMobile(string $raw, int $comCode, ?int $excludeId): ?string
    {
        if ($raw === '') return null;
        // Strip Excel formula wrappers
        if (preg_match('/^=""(.+)""$/', $raw, $m)) $raw = $m[1];
        elseif (preg_match('/^="(.+)"$/', $raw, $m)) $raw = $m[1];
        // Keep only the first number when multiple are separated by "/"
        $first = preg_split('#\s*/\s*#', $raw)[0];
        // Strip trailing labels like "Whats", "WhatsApp"
        $first = preg_replace('/\s*(Whats\w*)\s*.*/ui', '', $first);
        // Extract digits only (removes spaces, dashes, parentheses)
        $digits = preg_replace('/\D/', '', $first);
        if ($digits === '') return null;
        // Normalize 10-digit number starting with 1 → prepend leading zero
        if (strlen($digits) === 10 && str_starts_with($digits, '1')) $digits = '0' . $digits;
        // Accept only valid Egyptian mobile: exactly 11 digits starting with 01
        if (strlen($digits) !== 11 || !str_starts_with($digits, '01')) return null;
        // Skip if already used by a different employee in this company
        $query = Employee::where('emp_mobile', $digits)->where('com_code', $comCode);
        if ($excludeId) $query->where('id', '!=', $excludeId);
        if ($query->exists()) return null;
        return $digits;
    }

    private const ARABIC_MONTHS = [
        'يناير' => 'January', 'فبراير' => 'February', 'مارس'    => 'March',
        'أبريل' => 'April',   'مايو'   => 'May',       'يونيو'   => 'June',
        'يوليو' => 'July',    'أغسطس'  => 'August',    'سبتمبر' => 'September',
        'أكتوبر' => 'October', 'نوفمبر' => 'November', 'ديسمبر' => 'December',
    ];

    private function parseDate(?string $raw): ?string
    {
        if (empty($raw)) return null;
        $raw   = trim($raw);
        $lower = strtolower($raw);
        // Reject non-date strings
        if (in_array($lower, ['n/a', 'in process', '-', '']) || !preg_match('/\d/', $raw)) return null;

        // Translate Arabic month names to English
        $raw = str_replace(array_keys(self::ARABIC_MONTHS), array_values(self::ARABIC_MONTHS), $raw);

        try {
            $date = \Carbon\Carbon::parse($raw);
            // 2-digit years > current year mean previous century (e.g. 98 → 1998)
            if ($date->year > now()->year) {
                $date->subYears(100);
            }
            return $date->format('Y-m-d');
        } catch (\Exception) {
            return null;
        }
    }
}
