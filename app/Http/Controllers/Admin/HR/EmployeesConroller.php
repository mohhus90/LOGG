<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Jobs_categories;
use App\Models\Shifts_type;
use App\Models\Branche;
use App\Models\Client;
use App\Models\NameDictionary;
use App\Models\EmployeeDocument;
use App\Imports\EmployeeImport;
use App\Imports\EmployeeNidImport;
use App\Imports\EmployeeMedicalImport;
use App\Exports\EmployeeExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\SmsService;

class EmployeesConroller extends Controller

{
      public function export()
    {
        return Excel::download(new EmployeeExport, 'Employee.xlsx');
    }

    public function exportSystemCsv()
    {
        $comCode   = (int) Auth::guard('admin')->user()->com_code;
        $employees = Employee::with(['jobs_categories', 'client'])
            ->where('com_code', $comCode)
            ->get();

        // Pre-generate unique emails; reuse existing DB email only if not already taken
        $usedEmails = [];
        $emailMap   = [];
        foreach ($employees as $emp) {
            if (!empty($emp->emp_email) && !isset($usedEmails[$emp->emp_email])) {
                $emailMap[$emp->id]          = $emp->emp_email;
                $usedEmails[$emp->emp_email] = true;
            }
        }
        foreach ($employees as $emp) {
            if (!isset($emailMap[$emp->id])) {
                $emailMap[$emp->id] = $this->generateUniqueEmail($emp, $usedEmails);
            }
        }

        // Card No = bank_account; fallback → N0001, N0002, …
        $usedCardNos = [];
        $cardNoMap   = [];
        foreach ($employees as $emp) {
            if (!empty($emp->bank_account)) {
                $cardNoMap[$emp->id]             = $emp->bank_account;
                $usedCardNos[$emp->bank_account] = true;
            }
        }
        $nSeq = 1;
        foreach ($employees as $emp) {
            if (!isset($cardNoMap[$emp->id])) {
                do { $c = 'N' . str_pad($nSeq++, 8, '0', STR_PAD_LEFT); } while (isset($usedCardNos[$c]));
                $cardNoMap[$emp->id] = $c;
                $usedCardNos[$c]     = true;
            }
        }

        // Medical ID = medical_id; fallback → M0001, M0002, …
        $usedMedIds = [];
        $medIdMap   = [];
        foreach ($employees as $emp) {
            if (!empty($emp->medical_id)) {
                $medIdMap[$emp->id]           = $emp->medical_id;
                $usedMedIds[$emp->medical_id] = true;
            }
        }
        $mSeq = 1;
        foreach ($employees as $emp) {
            if (!isset($medIdMap[$emp->id])) {
                do { $c = '1' . str_pad($mSeq++, 8, '0', STR_PAD_LEFT); } while (isset($usedMedIds[$c]));
                $medIdMap[$emp->id] = $c;
                $usedMedIds[$c]     = true;
            }
        }

        $filename = 'System_Employees_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($employees, $emailMap, $cardNoMap, $medIdMap) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens Arabic correctly
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Name', 'namear', 'Mobile', 'emergency contact', 'emergency mobile',
                'Email', 'National Id', 'Medical Id', 'Social Number', 'social status',
                'Job', 'Salary', 'Date Of Birth', 'Gender', 'Marital Status',
                'Military Status', 'Company', 'Education', 'Cv', 'Extra Comments',
                'Username', 'Password', 'Status', 'Photo', 'Bank', 'Payment Channel',
                'Card No', 'Vacation Type', 'hire date', 'hrid', 'serial number',
                'Education Filed',
            ]);

            foreach ($employees as $emp) {
                fputcsv($out, $this->buildSystemCsvRow($emp, $emailMap[$emp->id] ?? '', $cardNoMap[$emp->id] ?? '', $medIdMap[$emp->id] ?? ''));
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildSystemCsvRow(Employee $emp, string $email = '', string $cardNo = '', string $medId = ''): array
    {
        return [
            $emp->employee_name_E                                ?: '',   // 0  Name
            $emp->employee_name_A                                ?: '',   // 1  namear
            $this->csvPhoneText($emp->emp_mobile),                        // 2  Mobile
            $emp->relative_relation                              ?: '',   // 3  emergency contact
            $this->csvPhoneText($emp->reference_mobile),                  // 4  emergency mobile
            $email,                                                        // 5  Email
            $this->csvNid($emp->national_id),                             // 6  National Id
            $this->csvText($medId),                                        // 7  Medical Id (or M0001…)
            $emp->insurance_no                                   ?: '',   // 8  Social Number
            (int) $emp->insurance_status === 1 ? 'Yes' : '',             // 9  social status
            optional($emp->jobs_categories)->job_name            ?: '',   // 10 Job
            $emp->emp_sal !== null ? number_format((float)$emp->emp_sal, 2, '.', '') : '', // 11 Salary
            $this->csvDateEn($emp->birth_date),                           // 12 Date Of Birth
            $this->csvGender($emp->emp_gender),                           // 13 Gender
            $this->csvMarital($emp->emp_social_status),                   // 14 Marital Status
            $this->csvMilitary($emp->emp_military_status),                // 15 Military Status
            optional($emp->client)->client_name                  ?: '',   // 16 Company
            $emp->emp_qualification                              ?: '',   // 17 Education
            $emp->emp_cv                                         ?: '',   // 18 Cv
            $emp->client_notes                                   ?: '',   // 19 Extra Comments
            $emp->employee_id                                    ?: '',   // 20 Username
            $this->csvNid($emp->national_id),                             // 21 Password (NID)
            $this->csvStatus($emp->functional_status),                    // 22 Status
            $emp->emp_photo                                      ?: '',   // 23 Photo
            $emp->bank_name                                      ?: '',   // 24 Bank
            $this->csvPayment($emp->sal_cash_visa),                       // 25 Payment Channel
            $this->csvText($cardNo),                                       // 26 Card No (bank_account or N0001…)
            $this->csvVacation($emp->vacation_formula),                   // 27 Vacation Type
            $this->csvDateEn($emp->emp_start_date),                       // 28 hire date
            $emp->employee_id                                    ?: '',   // 29 hrid
            $emp->employee_id                                    ?: '',   // 30 serial number
            '',                                                            // 31 Education Filed
        ];
    }

    private function csvDateEn($date): string
    {
        if (empty($date)) return '';
        try {
            return \Carbon\Carbon::parse((string) $date)->format('d/M/Y');
        } catch (\Exception) {
            return '';
        }
    }

    private function csvPhoneText($value): string
    {
        $phone = trim((string) ($value ?? ''));
        if ($phone === '') return '';
        $phone = preg_split('/[\/,]/', $phone)[0];
        $phone = trim($phone);
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') return '';
        if (!str_starts_with($digits, '0')) {
            $digits = '0' . $digits;
        }
        return $digits;
    }

    private function csvText(?string $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function csvNid(?string $value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function generateUniqueEmail(Employee $emp, array &$usedEmails): string
    {
        $parts = preg_split('/\s+/', trim($emp->employee_name_E ?? ''));
        $base  = preg_replace('/[^a-z0-9]/', '', strtolower($parts[0] ?? ''));
        if ($base === '') $base = 'emp' . $emp->id;
        $base  = substr($base, 0, 20);

        $email = "{$base}@trilogy.com";
        if (!isset($usedEmails[$email])) {
            $usedEmails[$email] = true;
            return $email;
        }

        $i = 2;
        while (isset($usedEmails["{$base}{$i}@trilogy.com"])) {
            $i++;
        }
        $email = "{$base}{$i}@trilogy.com";
        $usedEmails[$email] = true;
        return $email;
    }

    private function csvGender($v): string
    {
        return match((int) $v) { 1 => 'Male', 2 => 'Female', default => '' };
    }

    private function csvMarital($v): string
    {
        return match((int) $v) {
            1 => 'Single', 2 => 'Married', 3 => 'Widowed', 4 => 'Divorced', default => ''
        };
    }

    private function csvMilitary($v): string
    {
        return match((int) $v) {
            1 => 'Completed', 2 => 'Exempted', 3 => 'Postponed',
            4 => 'Exempted',  5 => 'Not Required', default => ''
        };
    }

    private function csvStatus($v): string
    {
        return match((int) $v) { 1 => 'Hired', 2 => 'Resigned', default => '' };
    }

    private function csvPayment($v): string
    {
        return match((int) $v) { 1 => 'Cash', 2 => 'Bank', default => '' };
    }

    private function csvVacation($v): string
    {
        return match((int) $v) { 1 => 'default', 2 => 'instant', default => '' };
    }

    public function getDictionary()
    {
        $comCode = (int) Auth::guard('admin')->user()->com_code;
        $entries = NameDictionary::where('com_code', $comCode)
            ->get(['ar_name', 'en_name']);
        return response()->json($entries);
    }

    public function saveDictionary(Request $request)
    {
        $comCode = (int) Auth::guard('admin')->user()->com_code;
        $entries = $request->validate(['entries' => 'required|array']);

        foreach ($request->entries as $entry) {
            if (empty($entry['ar']) || empty($entry['en'])) continue;
            NameDictionary::updateOrCreate(
                ['ar_name' => trim($entry['ar']), 'com_code' => $comCode],
                ['en_name' => trim($entry['en'])]
            );
        }

        return response()->json(['success' => true]);
    }
    

    
    /**
     * Display a listing of the resource.
     */
        public function index(Request $request)
    {
        $comCode = (int) Auth::guard('admin')->user()->com_code;

        // قوائم الفلاتر
        $departments     = Department::where('com_code', $comCode)->orderBy('dep_name')->get(['id','dep_name']);
        $jobs_categories = Jobs_categories::where('com_code', $comCode)->orderBy('job_name')->get(['id','job_name']);
        $branches        = Branche::where('com_code', $comCode)->orderBy('branch_name')->get(['id','branch_name']);
        $shifts          = Shifts_type::where('com_code', $comCode)->orderBy('type')->get(['id','type']);
        $clients         = Client::where('com_code', $comCode)->where('active', 1)->orderBy('client_name')->get(['id','client_name']);

        $query = Employee::where('com_code', $comCode);

        if ($request->filled('search_name')) {
            $q = '%'.$request->search_name.'%';
            $query->where(function($sq) use ($q) {
                $sq->where('employee_name_A','like',$q)->orWhere('employee_name_E','like',$q);
            });
        }
        if ($request->filled('search_code'))    $query->where('employee_id','like','%'.$request->search_code.'%');
        if ($request->filled('search_national'))$query->where('national_id','like','%'.$request->search_national.'%');
        if ($request->filled('search_phone'))   $query->where('phone','like','%'.$request->search_phone.'%');
        if ($request->filled('search_finger'))  $query->where('finger_id',$request->search_finger);
        if ($request->filled('search_branch'))  $query->where('branches_id',$request->search_branch);
        if ($request->filled('search_dept'))    $query->where('emp_departments_id',$request->search_dept);
        if ($request->filled('search_job'))     $query->where('emp_jobs_id',$request->search_job);
        if ($request->filled('search_shift'))   $query->where('shifts_types_id',$request->search_shift);
        if ($request->filled('search_func_status')) $query->where('functional_status',$request->search_func_status);
        if ($request->filled('search_gender'))      $query->where('emp_gender',$request->search_gender);
        if ($request->filled('search_insurance'))   $query->where('insurance_status',$request->search_insurance);
        if ($request->filled('search_has_finger'))  $query->where('is_has_finger',$request->search_has_finger);
        if ($request->filled('client_id'))          $query->where('client_id',$request->client_id);
        if ($request->filled('search_hrid'))        $query->where('hrid','like','%'.$request->search_hrid.'%');
        if ($request->filled('sal_from')) $query->where('emp_sal','>=',$request->sal_from);
        if ($request->filled('sal_to'))   $query->where('emp_sal','<=',$request->sal_to);
        if ($request->filled('hire_from'))$query->where('emp_start_date','>=',$request->hire_from);
        if ($request->filled('hire_to'))  $query->where('emp_start_date','<=',$request->hire_to);

        $allowed = ['employee_name_A','employee_id','emp_sal','emp_start_date','functional_status'];
        $sortBy  = in_array($request->sort_by, $allowed) ? $request->sort_by : 'employee_name_A';
        $sortDir = $request->sort_dir === 'desc' ? 'desc' : 'asc';
        $perPage = in_array((int)$request->per_page,[10,20,50,100]) ? (int)$request->per_page : 20;

        $data = $query->with(['documents' => fn($q) => $q->where('doc_type','photo')])
                     ->orderBy($sortBy,$sortDir)->paginate($perPage)->appends($request->except('page'));

        if ($request->ajax()) {
            return view('admin.employees.ajaxsearch', compact('data'))->render();
        }

        $totalSalary = Employee::where('com_code',$comCode)->sum('emp_sal');
        $totalActive = Employee::where('com_code',$comCode)->where('functional_status',1)->count();
        $totalAll    = Employee::where('com_code',$comCode)->count();

        return view('admin.employees.index', compact(
            'data','departments','jobs_categories','branches','shifts','clients',
            'totalSalary','totalActive','totalAll'
        ));
    }
    


     public function uploadexcel()
    {
       
        $data = Employee::with([
        'addedBy' => fn($q) => $q->select('id', 'name'),
        'updatedBy' => fn($q) => $q->select('id', 'name')
    ])->paginate(paginate_counter);
    
    return view('admin.employees.uploadexcel', compact('data'));
       
    }
    public function douploadexcel(Request $request)
    {
        $request->validate([
                    'excel_file' => 'required|mimes:xlsx,xls',
                ], [
                    'excel_file.required' => 'اختر ملف اكسيل',
                    'excel_file.mimes' => 'مطلوب اكسيل بامتداد (xlsx , xls) فقط',
                ]);

        Excel::import(new EmployeeImport, $request->excel_file);
        return redirect()->route('employees.index')
                    ->with('success', 'تم الإضافة بنجاح')->withInput();
    }

    public function updateNidFromExcel(Request $request)
    {
        $request->validate([
            'nid_file' => 'required|mimes:xlsx,xls,csv',
        ], [
            'nid_file.required' => 'اختر ملف Excel أو CSV',
            'nid_file.mimes'    => 'يجب أن يكون الملف بصيغة xlsx أو xls أو csv',
        ]);

        try {
            $comCode = (int) auth()->guard('admin')->user()->com_code;
            $import  = new EmployeeNidImport($comCode);
            Excel::import($import, $request->file('nid_file'));

            $msg = "تم تحديث {$import->updated} موظف | غير موجود: {$import->notFound} | صفوف فارغة: {$import->skipped}";

            if ($import->errors > 0) {
                $msg .= " | أخطاء: {$import->errors}";
                if (!empty($import->errorDetails)) {
                    $msg .= ' (' . implode(' / ', array_slice($import->errorDetails, 0, 3)) . ')';
                }
                return redirect()->route('employees.uploadexcel')->with('error', $msg);
            }

            return redirect()->route('employees.uploadexcel')->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('NID import error: ' . $e->getMessage());
            return redirect()->route('employees.uploadexcel')
                ->with('error', 'فشل استيراد الملف: ' . $e->getMessage());
        }
    }

    public function doUploadMedicalExcel(Request $request)
    {
        $request->validate([
            'medical_file' => 'required|mimes:xlsx,xls,csv',
        ], [
            'medical_file.required' => 'اختر ملف Excel أو CSV',
            'medical_file.mimes'    => 'يجب أن يكون الملف بصيغة xlsx أو xls أو csv',
        ]);

        try {
            $comCode = (int) auth()->guard('admin')->user()->com_code;
            $adminId = (int) auth()->guard('admin')->user()->id;
            $import  = new EmployeeMedicalImport($comCode, $adminId);
            Excel::import($import, $request->file('medical_file'));

            $msg = "تم تحديث {$import->updated} موظف | غير موجود: {$import->notFound} | تخطي: {$import->skipped}";

            if ($import->errors > 0) {
                $msg .= " | أخطاء: {$import->errors}";
                if (!empty($import->errorDetails)) {
                    $msg .= ' (' . implode(' / ', array_slice($import->errorDetails, 0, 3)) . ')';
                }
                return redirect()->route('employees.uploadexcel')->with('error', $msg);
            }

            return redirect()->route('employees.uploadexcel')->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Medical import error: ' . $e->getMessage());
            return redirect()->route('employees.uploadexcel')
                ->with('error', 'فشل استيراد الملف: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $com_code        = auth()->guard('admin')->user()->com_code;
        $departments     = Department::where('com_code', $com_code)->get(['id', 'dep_name']);
        $jobs_categories = Jobs_categories::where('com_code', $com_code)->get(['id', 'job_name']);
        $shifts_types    = Shifts_type::where('com_code', $com_code)->get(['id', 'type', 'from_time', 'to_time', 'total_hour']);
        $branches        = Branche::where('com_code', $com_code)->get(['id', 'branch_name']);
        $clients         = Client::where('com_code', $com_code)->where('active', 1)->get(['id', 'client_name']);
        return view('admin.employees.create', compact('shifts_types', 'departments', 'jobs_categories', 'branches', 'clients'));
    }
    /**
     * Store a newly created resource in storage.
     */


public function store(Request $request)
{
    // التحقق من صحة البيانات قبل الدخول في الـ try/catch
    $request->validate([
        'employee_name_A' => 'required|string',
        'employee_name_E' => 'nullable|string',
        'employee_id' => 'required|unique:employees,employee_id',
        'national_id' => 'nullable|unique:employees,national_id',
        'insurance_no' => 'nullable|unique:employees,insurance_no',
        'bank_account' => 'nullable|unique:employees,bank_account',
        'emp_jobs_id' => 'required|exists:jobs_categories,id',
        'finger_id' => 'nullable|string',
        'employee_address' => 'nullable|string',
        'emp_gender' => 'nullable|string',
        'emp_social_status' => 'nullable|string',
        'emp_start_date' => 'nullable|date',
        'insurance_status' => 'nullable|string',
        'functional_status' => 'nullable|string',
        'resignation_status' => 'nullable|string',
        'qualification_grade' => 'nullable|string',
        'emp_military_status' => 'nullable|string',
        'motivation' => 'nullable|numeric',
        'sal_cash_visa' => 'nullable|string',
        'bank_name' => 'nullable|string',
        'bank_ID' => 'nullable|string',
        'bank_branch' => 'nullable|string',
    ], [
        'employee_name_A.required' => 'حقل اسم الموظف مطلوب',
        'employee_id.required' => 'حقل كود الموظف مطلوب',
        'employee_id.unique' => 'كود الموظف تم إدخاله مسبقًا',
        'national_id.unique' => 'هذا الرقم القومي تم إدخاله مسبقًا',
        'bank_account.unique' => 'هذا الحساب البنكي تم إدخاله مسبقًا',
        'emp_jobs_id.required' => 'حقل الوظيفة مطلوب',
        'emp_jobs_id.exists' => 'الوظيفة المحددة غير موجودة',

    ]);

    DB::beginTransaction();

    try {
        // ✅ تجهيز البيانات للحفظ
        $employeeData = [
            'added_by' => auth()->guard('admin')->user()->id,
            'com_code' => auth()->guard('admin')->user()->com_code,
            'employee_id' => $request->employee_id,
            'finger_id' => $request->finger_id,
            'employee_name_A' => $request->employee_name_A,
            'employee_name_E' => $request->employee_name_E,
            'employee_address' => $request->employee_address,
            'emp_gender' => $request->emp_gender,
            'emp_social_status' => $request->emp_social_status,
            'emp_start_date' => $request->emp_start_date,
            'insurance_status' => $request->insurance_status,
            'functional_status' => $request->functional_status,
            'resignation_status' => $request->resignation_status,
            'qualification_grade' => $request->qualification_grade,
            'emp_qualification' => $request->emp_qualification,
            'qualification_year' => $request->qualification_year,
            'resignation_date' => $request->resignation_date,
            'resignation_cause' => $request->resignation_cause,
            'emp_home_tel' => $request->emp_home_tel,
            'emp_mobile' => $request->emp_mobile,
            'emp_email' => $request->emp_email,
            'emp_photo' => null,
            'birth_date' => $request->birth_date,
            'emp_sal' => $request->emp_sal,
            'emp_sal_insurance' => $request->emp_sal_insurance,
            'medical_insurance' => $request->medical_insurance,
            'emp_fixed_allowances' => $request->emp_fixed_allowances,
            'emp_military_status' => $request->emp_military_status,
            'motivation' => $request->motivation,
            'national_id' => $request->national_id,
            'insurance_no' => $request->insurance_no,
            'sal_cash_visa' => $request->sal_cash_visa,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'bank_ID' => $request->bank_ID,
            'bank_branch' => $request->bank_branch,
            'daily_work_hours'        => $request->daily_work_hours,
            'emp_departments_id'       => $request->emp_departments_id ?: null,
            'emp_jobs_id'              => $request->emp_jobs_id,
            'shifts_types_id'          => $request->shifts_types_id,
            'branches_id'              => $request->branches_id ?: null,
            // Client-specific fields
            'client_id'               => $request->client_id ?: null,
            'hrid'                    => $request->hrid ?: null,
            'reference_mobile'        => $request->reference_mobile ?: null,
            'relative_relation'       => $request->relative_relation ?: null,
            'hiring_documents_status' => $request->hiring_documents_status ?: null,
            'insurance_start_date'    => $request->insurance_start_date ?: null,
            'insurance_end_date'      => $request->insurance_end_date ?: null,
            'form1_notes'             => $request->form1_notes ?: null,
            'form6_notes'             => $request->form6_notes ?: null,
            'client_notes'            => $request->client_notes ?: null,
            'medical_id'              => $request->medical_id ?: null,
            'medical_status'          => $request->medical_status ?: null,
            'medical_progress'        => $request->medical_progress ?: null,
            'apply_income_tax'        => $request->boolean('apply_income_tax'),
            'probation_end_date'      => $request->probation_end_date ?: null,
            'contract_end_date'       => $request->contract_end_date ?: null,
            'created_at'               => now(),
            'updated_at'               => now(),
        ];

        // ✅ حفظ البيانات
        $newEmployee = Employee::create($employeeData);

        DB::commit();

        // SMS ترحيب
        if ($newEmployee->emp_mobile) {
            try {
                (new SmsService((int)auth()->guard('admin')->user()->com_code))
                    ->sendWelcomeEmployee($newEmployee->emp_mobile, $newEmployee->employee_name_A);
            } catch (\Exception $e) {
                Log::warning('SMS welcome failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('employees.edit', $newEmployee->id)
            ->with('success', 'تم إضافة الموظف بنجاح — يمكنك الآن رفع ملفات التعيين من الأسفل');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error during employee save: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'حدث خطأ غير متوقع: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = Employee::with(['client', 'documents'])->where(['id' => $id])->first();
        if (empty($data)) {
            return redirect()->back()->with(['error' => 'عفوا حدث خطأ '])->withInput();
        }
        $com_code        = auth()->guard('admin')->user()->com_code;
        $departments     = Department::where('com_code', $com_code)->get(['id', 'dep_name']);
        $jobs_categories = Jobs_categories::where('com_code', $com_code)->get(['id', 'job_name']);
        $shifts_types    = Shifts_type::where('com_code', $com_code)->get(['id', 'type']);
        $branches        = Branche::where('com_code', $com_code)->get(['id', 'branch_name']);
        $documents       = $data->documents->keyBy('doc_type');
        $docTypes        = EmployeeDocument::TYPES;
        return view('admin.employees.show', compact('data', 'shifts_types', 'departments', 'jobs_categories', 'branches', 'documents', 'docTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Employee::with('documents')->where(['id' => $id])->first();
        if (empty($data)) {
            return redirect()->back()->with(['error' => 'عفوا حدث خطأ '])->withInput();
        }
        $com_code        = auth()->guard('admin')->user()->com_code;
        $departments     = Department::where('com_code', $com_code)->get(['id', 'dep_name']);
        $jobs_categories = Jobs_categories::where('com_code', $com_code)->get(['id', 'job_name']);
        $shifts_types    = Shifts_type::where('com_code', $com_code)->get(['id', 'type']);
        $branches        = Branche::where('com_code', $com_code)->get(['id', 'branch_name']);
        $clients         = Client::where('com_code', $com_code)->where('active', 1)->get(['id', 'client_name']);
        $documents       = $data->documents->keyBy('doc_type');
        $docTypes        = EmployeeDocument::TYPES;
        return view('admin.employees.update', compact('data', 'shifts_types', 'departments', 'jobs_categories', 'branches', 'clients', 'documents', 'docTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    // التحقق من الحقول المكررة
    $validator1 = Validator::make($request->all(), [
        'employee_id' => ['required', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator1->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال كود الموظف هذا لموظف آخر'])->withInput();
    }

    $validator2 = Validator::make($request->all(), [
        'national_id' => ['nullable', Rule::unique('employees')->ignore($id)->whereNotNull('national_id')],
    ]);
    if ($validator2->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال الرقم القومي هذا لموظف آخر'])->withInput();
    }

    $validator3 = Validator::make($request->all(), [
        'insurance_no' => ['nullable', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator3->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال الرقم التأميني هذا لموظف آخر'])->withInput();
    }

    $validator4 = Validator::make($request->all(), [
        'bank_account' => ['nullable', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator4->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال حساب البنك هذا لموظف آخر'])->withInput();
    }

    // التحقق من الحقول الأخرى
    $request->validate([
        'employee_name_A' => 'required|string',
        'employee_name_E' => 'required|string',
        'employee_id' => 'required',
        'national_id' => 'nullable',
        'emp_jobs_id' => 'required|exists:jobs_categories,id',
        'finger_id' => 'nullable|string',
        'employee_address' => 'nullable|string',
        'emp_gender' => 'nullable|string',
        'emp_social_status' => 'nullable|string',
        'emp_start_date' => 'nullable|date',
        'functional_status' => 'nullable|string',
        'insurance_status' => 'nullable|string',
        'resignation_status' => 'nullable|string',
        'qualification_grade' => 'nullable|string',
        'emp_military_status' => 'nullable|string',
        'motivation' => 'nullable|numeric',
        'sal_cash_visa' => 'nullable|string',
        'bank_name' => 'nullable|string',
        'bank_ID' => 'nullable|string',
        'bank_branch' => 'nullable|string',
    ], [
        'employee_name_A.required' => 'حقل اسم الموظف مطلوب',
        'employee_name_E.required' => 'حقل اسم الموظف مطلوب',
        'employee_id.required' => 'حقل كود الموظف مطلوب',
        'national_id.required' => 'حقل الرقم القومي مطلوب',
        'emp_jobs_id.required' => 'حقل الوظيفة مطلوب',
        'emp_jobs_id.exists' => 'الوظيفة المحددة غير موجودة',

    ]);

    DB::beginTransaction();

    try {
        $employee = Employee::findOrFail($id);

        // بيانات التحديث
        $dataupdate = [
            'updated_by' => auth()->guard('admin')->user()->id,
            'com_code' => auth()->guard('admin')->user()->com_code,
            'employee_id' => $request->employee_id,
            'finger_id' => $request->finger_id,
            'employee_name_A' => $request->employee_name_A,
            'employee_name_E' => $request->employee_name_E,
            'employee_address' => $request->employee_address,
            'emp_gender' => $request->emp_gender,
            'emp_social_status' => $request->emp_social_status,
            'emp_start_date' => $request->emp_start_date,
            'functional_status' => $request->functional_status,
            'insurance_status' => $request->insurance_status,
            'resignation_status' => $request->resignation_status,
            'qualification_grade' => $request->qualification_grade,
            'emp_qualification' => $request->emp_qualification,
            'qualification_year' => $request->qualification_year,
            'resignation_date' => $request->resignation_date,
            'resignation_cause' => $request->resignation_cause,
            'emp_home_tel' => $request->emp_home_tel,
            'emp_mobile' => $request->emp_mobile,
            'emp_email' => $request->emp_email,
            'emp_photo' => $employee->emp_photo,
            'birth_date' => $request->birth_date,
            'emp_sal' => $request->emp_sal,
            'emp_sal_insurance' => $request->emp_sal_insurance,
            'medical_insurance' => $request->medical_insurance,
            'emp_fixed_allowances' => $request->emp_fixed_allowances,
            'emp_military_status' => $request->emp_military_status,
            'motivation' => $request->motivation,
            'national_id' => $request->national_id,
            'insurance_no' => $request->insurance_no,
            'sal_cash_visa' => $request->sal_cash_visa,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'bank_ID' => $request->bank_ID,
            'bank_branch' => $request->bank_branch,
            'daily_work_hours' => $request->daily_work_hours,
            'emp_departments_id' => $request->emp_departments_id,
            'emp_jobs_id' => $request->emp_jobs_id,
            'shifts_types_id' => $request->shifts_types_id,
            'branches_id' => $request->branches_id,
            'custom_overtime_multiplier'  => $request->custom_overtime_multiplier ?: null,
            'overtime_enabled'            => $request->overtime_enabled           ?? 1,
            'late_deduction_enabled'      => $request->late_deduction_enabled     ?? 1,
            'weekly_off_day'              => $request->weekly_off_day !== '' && $request->filled('weekly_off_day') ? (int)$request->weekly_off_day : null,
            // Client-specific fields
            'client_id'               => $request->client_id ?: null,
            'hrid'                    => $request->hrid ?: null,
            'reference_mobile'        => $request->reference_mobile ?: null,
            'relative_relation'       => $request->relative_relation ?: null,
            'hiring_documents_status' => $request->hiring_documents_status ?: null,
            'insurance_start_date'    => $request->insurance_start_date ?: null,
            'insurance_end_date'      => $request->insurance_end_date ?: null,
            'form1_notes'             => $request->form1_notes ?: null,
            'form6_notes'             => $request->form6_notes ?: null,
            'client_notes'            => $request->client_notes ?: null,
            'medical_id'              => $request->medical_id ?: null,
            'medical_status'          => $request->medical_status ?: null,
            'medical_progress'        => $request->medical_progress ?: null,
            'apply_income_tax'        => $request->boolean('apply_income_tax'),
            'probation_end_date'      => $request->probation_end_date ?: null,
            'contract_end_date'       => $request->contract_end_date ?: null,
            'updated_at'              => now(),
        ];

        // تحديث البيانات
        $employee->update($dataupdate);

        DB::commit();
        return redirect()->route('employees.index')->with(['success' => 'تم التحديث بنجاح']);

    } catch (\Exception $ex) {
        DB::rollBack();
        return redirect()->back()->with(['error' => 'عفواً حدث خطأ: ' . $ex->getMessage()])->withInput();
    }
}

    public function deleteFiltered(Request $request)
    {
        $comCode = (int) Auth::guard('admin')->user()->com_code;

        $query = Employee::where('com_code', $comCode);

        if ($request->filled('search_name')) {
            $q = '%'.$request->search_name.'%';
            $query->where(function($sq) use ($q) {
                $sq->where('employee_name_A','like',$q)->orWhere('employee_name_E','like',$q);
            });
        }
        if ($request->filled('search_code'))        $query->where('employee_id','like','%'.$request->search_code.'%');
        if ($request->filled('search_national'))    $query->where('national_id','like','%'.$request->search_national.'%');
        if ($request->filled('search_phone'))       $query->where('phone','like','%'.$request->search_phone.'%');
        if ($request->filled('search_finger'))      $query->where('finger_id',$request->search_finger);
        if ($request->filled('search_branch'))      $query->where('branches_id',$request->search_branch);
        if ($request->filled('search_dept'))        $query->where('emp_departments_id',$request->search_dept);
        if ($request->filled('search_job'))         $query->where('emp_jobs_id',$request->search_job);
        if ($request->filled('search_shift'))       $query->where('shifts_types_id',$request->search_shift);
        if ($request->filled('search_func_status')) $query->where('functional_status',$request->search_func_status);
        if ($request->filled('search_gender'))      $query->where('emp_gender',$request->search_gender);
        if ($request->filled('search_insurance'))   $query->where('insurance_status',$request->search_insurance);
        if ($request->filled('search_has_finger'))  $query->where('is_has_finger',$request->search_has_finger);
        if ($request->filled('client_id'))          $query->where('client_id',$request->client_id);
        if ($request->filled('search_hrid'))        $query->where('hrid','like','%'.$request->search_hrid.'%');
        if ($request->filled('sal_from'))           $query->where('emp_sal','>=',$request->sal_from);
        if ($request->filled('sal_to'))             $query->where('emp_sal','<=',$request->sal_to);
        if ($request->filled('hire_from'))          $query->where('emp_start_date','>=',$request->hire_from);
        if ($request->filled('hire_to'))            $query->where('emp_start_date','<=',$request->hire_to);

        // منع الحذف بدون أي فلتر مفعّل (لتجنب حذف جميع الموظفين بالخطأ)
        $filterKeys = ['search_name','search_code','search_national','search_phone','search_finger',
                       'search_branch','search_dept','search_job','search_shift','search_func_status',
                       'search_gender','search_insurance','search_has_finger','client_id','search_hrid',
                       'sal_from','sal_to','hire_from','hire_to'];
        $hasFilter = collect($filterKeys)->some(fn($k) => $request->filled($k));

        if (!$hasFilter) {
            return redirect()->route('employees.index')->with('error', 'يجب تطبيق فلتر واحد على الأقل قبل الحذف الجماعي');
        }

        try {
            DB::beginTransaction();
            $count = $query->count();
            $query->delete();
            DB::commit();
            return redirect()->route('employees.index')->with('success', "تم حذف {$count} موظف بنجاح");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('deleteFiltered: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        try{
            $data=Employee::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput();
            }
            Employee::where(['id'=>$id])->delete();
            return redirect()->route('employees.index')->with(['success' => 'تم حذف الوظيفة بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>' عفوا حدث خطأ ما '.$ex->getMessage()])->withInput();
        }
    }

    // ── Document Management ─────────────────────────────────────

    public function uploadDocument(Request $request, $id)
    {
        $isPhoto = $request->doc_type === 'photo';
        $request->validate([
            'doc_type' => 'required|in:' . implode(',', array_keys(EmployeeDocument::TYPES)),
            'doc_file' => ['required', 'file', 'max:10240',
                $isPhoto ? 'mimes:jpg,jpeg,png,gif' : 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], [
            'doc_file.required' => 'يجب اختيار ملف',
            'doc_file.mimes'    => $isPhoto ? 'يجب أن تكون الصورة من نوع JPG أو PNG' : 'يجب أن يكون الملف من نوع PDF، صورة، أو Word',
            'doc_file.max'      => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت',
        ]);

        $employee = Employee::findOrFail($id);
        $com_code = auth()->guard('admin')->user()->com_code;
        $admin_id = auth()->guard('admin')->user()->id;

        $file     = $request->file('doc_file');
        $docType  = $request->doc_type;
        $origName = $file->getClientOriginalName();
        $ext      = $file->getClientOriginalExtension();
        $fileName = 'emp_' . $id . '_' . $docType . '_' . time() . '.' . $ext;
        $destPath = public_path('assets/admin/employee_docs');

        if (!is_dir($destPath)) mkdir($destPath, 0755, true);
        $file->move($destPath, $fileName);

        // Replace existing doc of same type
        $existing = EmployeeDocument::where('employee_id', $id)->where('doc_type', $docType)->first();
        if ($existing) {
            $oldPath = public_path('assets/admin/employee_docs/' . basename($existing->doc_path));
            if (file_exists($oldPath)) @unlink($oldPath);
            $existing->update([
                'doc_original_name' => $origName,
                'doc_path'          => 'assets/admin/employee_docs/' . $fileName,
                'added_by'          => $admin_id,
            ]);
        } else {
            EmployeeDocument::create([
                'employee_id'       => $id,
                'doc_type'          => $docType,
                'doc_original_name' => $origName,
                'doc_path'          => 'assets/admin/employee_docs/' . $fileName,
                'com_code'          => $com_code,
                'added_by'          => $admin_id,
            ]);
        }

        return redirect()->route('employees.show', $id)->with(['success' => 'تم رفع الملف بنجاح']);
    }

    public function downloadDocument($id, $docId)
    {
        $doc = EmployeeDocument::where('id', $docId)->where('employee_id', $id)->firstOrFail();
        $path = public_path($doc->doc_path);
        if (!file_exists($path)) {
            return redirect()->back()->with(['error' => 'الملف غير موجود']);
        }
        return response()->download($path, $doc->doc_original_name);
    }

    public function deleteDocument($id, $docId)
    {
        $doc = EmployeeDocument::where('id', $docId)->where('employee_id', $id)->firstOrFail();
        $path = public_path($doc->doc_path);
        if (file_exists($path)) @unlink($path);
        $doc->delete();
        return redirect()->route('employees.show', $id)->with(['success' => 'تم حذف الملف بنجاح']);
    }
    
    // public function ajaxsearch(Request $request){
       
    //     if($request->ajax()){
    //         $employee_name_A_search=$request->employee_name_A_search;
    //         if($employee_name_A_search=='all'){
    //             $field1="id";
    //             $op1=">";
    //             $val1=0;
    //         }else{
    //             $field1="employee_name_A";
    //             $op1="like";
    //             $val1='%' . $employee_name_A_search . '%';
    //         }
    //         $data = Employee::select("*")
    //         ->where($field1, $op1, $val1)
    //         ->orderBy("id", "DESC")
    //         ->paginate(paginate_counter);
    
    //             if ($request->ajax()) {
    //                 // Return partial view for AJAX request
    //                return view('admin.employees.ajaxsearch', ['data' => $data]);
    //             } else {
    //                 // Return full view for initial load or page refresh
    //                 return view('admin.employees.index', ['data' => $data]);
    //             }
    //     }
        
    // }
}
