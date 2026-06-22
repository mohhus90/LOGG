<?php

namespace App\Http\Controllers\Admin;

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
            'csv_file' => 'required|file|mimes:csv,txt',
        ], [
            'csv_file.required' => 'يجب رفع ملف CSV',
            'csv_file.mimes'    => 'يجب أن يكون الملف بصيغة CSV',
        ]);

        $client   = Client::findOrFail($id);
        $com_code = auth()->guard('admin')->user()->com_code;
        $admin_id = auth()->guard('admin')->user()->id;

        // Get or create default shift
        $defaultShift = DB::table('shifts_types')->where('com_code', $com_code)->first();
        if (!$defaultShift) {
            return redirect()->back()->with(['error' => 'يجب إضافة نوع وردية أولاً قبل استيراد الموظفين']);
        }

        $file     = $request->file('csv_file');
        $path     = $file->getRealPath();
        $handle   = fopen($path, 'r');

        // Read header row
        $header = fgetcsv($handle);
        // Strip BOM if present
        if ($header && str_starts_with($header[0], "\xef\xbb\xbf")) {
            $header[0] = substr($header[0], 3);
        }
        $header = array_map('trim', $header);

        $imported = 0;
        $updated  = 0;
        $errors   = [];
        $rowNum   = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 5) continue;
            $data = array_combine($header, array_pad($row, count($header), null));

            // Skip empty rows (no name)
            $englishName = trim($data['English Name'] ?? '');
            $arabicName  = trim($data['Arabic Name'] ?? '');
            if (empty($englishName) && empty($arabicName)) continue;

            // ── Normalize NID (Excel may export large numbers as scientific notation) ──
            $nid = trim($data['NID'] ?? '');
            if (!empty($nid) && preg_match('/^[\d.]+[Ee][+\-]?\d+$/', $nid)) {
                $nid = '';
            }

            // ── Identifiers ──
            $fakeId = trim($data['Fake ID'] ?? '');
            $hrid   = trim($data['HRID'] ?? '');

            // ── Find existing employee: NID → Fake ID → HRID ──
            $existingEmployee = null;
            if (!empty($nid)) {
                $existingEmployee = Employee::where('com_code', $com_code)->where('national_id', $nid)->first();
            }
            if (!$existingEmployee && !empty($fakeId)) {
                $existingEmployee = Employee::where('employee_id', $fakeId)->first();
            }
            if (!$existingEmployee && !empty($hrid)) {
                $existingEmployee = Employee::where('hrid', $hrid)->where('client_id', $client->id)->first();
            }

            // ── Map status ──
            $statusRaw = strtolower(trim($data['Status'] ?? 'active'));
            $functional_status  = 1;
            $resignation_status = null;
            if (in_array($statusRaw, ['resigned', 'terminated'])) {
                $functional_status  = 2;
                $resignation_status = ($statusRaw === 'terminated') ? 2 : 1;
            }

            // ── Map gender ──
            $genderRaw  = strtolower(trim($data['Gender'] ?? ''));
            $emp_gender = ($genderRaw === 'female') ? 2 : 1;

            // ── Map marital status ──
            $maritalRaw = strtolower(trim($data['Marital Status'] ?? ''));
            $emp_social_status = 1;
            if ($maritalRaw === 'married') $emp_social_status = 2;
            elseif (in_array($maritalRaw, ['divorced', 'widowed'])) $emp_social_status = 3;

            // ── Map military certificate (temporary exempted BEFORE exempted) ──
            $militaryRaw = strtolower(trim($data['Military Certificate'] ?? ''));
            $emp_military_status = null;
            if ($emp_gender === 2) {
                $emp_military_status = null;
            } elseif (str_contains($militaryRaw, 'serve completed')) {
                $emp_military_status = 1;
            } elseif (str_contains($militaryRaw, 'temporary exempted')) {
                $emp_military_status = 4;
            } elseif (str_contains($militaryRaw, 'exempted')) {
                $emp_military_status = 2;
            } elseif (str_contains($militaryRaw, 'postponed')) {
                $emp_military_status = 3;
            } elseif (str_contains($militaryRaw, 'not required')) {
                $emp_military_status = 5;
            }

            // ── Parse dates ──
            $emp_start_date   = $this->parseDate($data['Hiring Date'] ?? '');
            $resignation_date = $this->parseDate($data['Resignation Date'] ?? '');
            $birth_date       = $this->parseDate($data['Date Of Birth'] ?? '');
            $insurance_start  = $this->parseDate($data['Start Date Of Social'] ?? '');
            $insurance_end    = $this->parseDate($data['End Date Of Social'] ?? '');

            // ── Get or create job category ──
            $positionName = trim($data['Position'] ?? '') ?: 'General';
            $job = Jobs_categories::firstOrCreate(
                ['job_name' => $positionName, 'com_code' => $com_code],
                ['added_by' => $admin_id]
            );
            $job_id = $job->id;

            // ── Mobile: take first number only; skip if used by a different employee ──
            $mobile = trim($data['Mobile'] ?? '');
            $mobile = trim(explode('/', $mobile)[0]);
            $mobile = trim(explode(' Whats', $mobile)[0]);
            $mobile = preg_replace('/\s+/', '', $mobile);
            if (!empty($mobile)) {
                $mobileQuery = Employee::where('emp_mobile', $mobile)->where('com_code', $com_code);
                if ($existingEmployee) $mobileQuery->where('id', '!=', $existingEmployee->id);
                if ($mobileQuery->exists()) $mobile = null;
            }

            // ── Insurance number: skip if used by a different employee ──
            $insuranceNo = trim($data['Social Number'] ?? '');
            if (!empty($insuranceNo) && (strtolower($insuranceNo) === 'n/a' || !is_numeric($insuranceNo))) {
                $insuranceNo = null;
            }
            if (!empty($insuranceNo)) {
                $insQuery = Employee::where('insurance_no', $insuranceNo)->where('com_code', $com_code);
                if ($existingEmployee) $insQuery->where('id', '!=', $existingEmployee->id);
                if ($insQuery->exists()) $insuranceNo = null;
            }

            // ── Fields shared between insert and update ──
            $fields = [
                'employee_name_E'         => $englishName ?: '-',
                'employee_name_A'         => $arabicName ?: $englishName ?: '-',
                'employee_address'        => trim($data['Address'] ?? ''),
                'emp_gender'              => $emp_gender,
                'emp_social_status'       => $emp_social_status,
                'emp_military_status'     => $emp_military_status,
                'emp_start_date'          => $emp_start_date,
                'functional_status'       => $functional_status,
                'resignation_status'      => $resignation_status,
                'resignation_date'        => $resignation_date,
                'birth_date'              => $birth_date,
                'national_id'             => !empty($nid) ? $nid : null,
                'insurance_no'            => $insuranceNo,
                'emp_mobile'              => $mobile ?: null,
                'emp_jobs_id'             => $job_id,
                'client_id'               => $client->id,
                'hrid'                    => $hrid ?: null,
                'reference_mobile'        => trim($data['Reference Number'] ?? '') ?: null,
                'relative_relation'       => trim($data['Relative'] ?? '') ?: null,
                'hiring_documents_status' => trim($data['Hiring Documents'] ?? '') ?: null,
                'insurance_start_date'    => $insurance_start,
                'insurance_end_date'      => $insurance_end,
                'form1_notes'             => trim($data['Form 1 Comments'] ?? '') ?: null,
                'form6_notes'             => trim($data['Form 6 Comments'] ?? '') ?: null,
                'client_notes'            => trim($data['Comments'] ?? '') ?: null,
            ];

            try {
                if ($existingEmployee) {
                    // ── UPDATE existing employee ──
                    // Sync employee_id to Fake ID if provided and not taken by another record
                    if (!empty($fakeId) && $existingEmployee->employee_id !== $fakeId) {
                        $idTaken = Employee::where('employee_id', $fakeId)
                            ->where('id', '!=', $existingEmployee->id)
                            ->exists();
                        if (!$idTaken) {
                            $fields['employee_id'] = $fakeId;
                        }
                    }
                    $fields['updated_by'] = $admin_id;
                    $existingEmployee->update($fields);
                    $updated++;
                } else {
                    // ── INSERT new employee ──
                    if (!empty($fakeId)) {
                        $employee_id = $fakeId;
                    } else {
                        $last = Employee::where('com_code', $com_code)->max('id') ?? 0;
                        $employee_id = 'EMP-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
                    }
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

        fclose($handle);

        $message = "تم إضافة {$imported} موظف جديد. تم تحديث {$updated} موظف موجود.";
        if (!empty($errors)) {
            $message .= ' أخطاء: ' . implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $message .= ' ... و ' . (count($errors) - 5) . ' أخطاء أخرى';
        }

        return redirect()->route('clients.index')->with(['success' => $message]);
    }

    // ── Helpers ──────────────────────────────────────────────────

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
        } catch (\Exception $e) {
            return null;
        }
    }
}
