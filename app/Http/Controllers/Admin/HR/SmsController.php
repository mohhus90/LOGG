<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Jobs_categories;
use App\Models\Branche;
use App\Models\Shifts_type;
use App\Models\Client;
use App\Models\Admin_panel_setting;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    // ─────────────────────────────────────────────
    //  شاشة الإرسال الرئيسية
    // ─────────────────────────────────────────────
    public function compose()
    {
        $comCode     = $this->comCode();
        $setting     = Admin_panel_setting::getByComCode($comCode);
        $smsEnabled  = $setting && $setting->sms_enabled && $setting->sms_username;

        $departments     = Department::where('com_code', $comCode)->orderBy('dep_name')->get(['id','dep_name']);
        $jobs_categories = Jobs_categories::where('com_code', $comCode)->orderBy('job_name')->get(['id','job_name']);
        $branches        = Branche::where('com_code', $comCode)->orderBy('branch_name')->get(['id','branch_name']);
        $shifts          = Shifts_type::where('com_code', $comCode)->orderBy('type')->get(['id','type']);
        $clients         = Client::where('com_code', $comCode)->where('active', 1)->orderBy('client_name')->get(['id','client_name']);

        return view('admin.sms.compose', compact(
            'smsEnabled', 'departments', 'jobs_categories', 'branches', 'shifts', 'clients', 'setting'
        ));
    }

    // ─────────────────────────────────────────────
    //  AJAX: جلب الموظفين حسب الفلاتر
    // ─────────────────────────────────────────────
    public function filterEmployees(Request $request)
    {
        $comCode = $this->comCode();
        $query   = Employee::where('com_code', $comCode);

        if ($request->filled('search_name')) {
            $q = '%' . $request->search_name . '%';
            $query->where(fn($sq) => $sq->where('employee_name_A', 'like', $q)->orWhere('employee_name_E', 'like', $q));
        }
        if ($request->filled('search_code'))         $query->where('employee_id', 'like', '%' . $request->search_code . '%');
        if ($request->filled('search_phone'))         $query->where('emp_mobile', 'like', '%' . $request->search_phone . '%');
        if ($request->filled('search_dept'))          $query->where('emp_departments_id', $request->search_dept);
        if ($request->filled('search_branch'))        $query->where('branches_id', $request->search_branch);
        if ($request->filled('search_job'))           $query->where('emp_jobs_id', $request->search_job);
        if ($request->filled('search_shift'))         $query->where('shifts_types_id', $request->search_shift);
        if ($request->filled('search_func_status'))   $query->where('functional_status', $request->search_func_status);
        if ($request->filled('search_gender'))        $query->where('emp_gender', $request->search_gender);
        if ($request->filled('client_id'))            $query->where('client_id', $request->client_id);
        if ($request->filled('has_phone')) {
            if ($request->has_phone === 'yes')  $query->whereNotNull('emp_mobile')->where('emp_mobile', '!=', '');
            if ($request->has_phone === 'no')   $query->where(fn($q) => $q->whereNull('emp_mobile')->orWhere('emp_mobile', ''));
        }

        $employees = $query->with(['department', 'jobs_categories'])
            ->orderBy('employee_name_A')
            ->get(['id','employee_name_A','employee_id','emp_mobile','emp_departments_id','emp_jobs_id','functional_status']);

        return response()->json([
            'count'     => $employees->count(),
            'withPhone' => $employees->filter(fn($e) => !empty($e->emp_mobile))->count(),
            'employees' => $employees->map(fn($e) => [
                'id'           => $e->id,
                'name'         => $e->employee_name_A,
                'code'         => $e->employee_id,
                'phone'        => $e->emp_mobile ?? '',
                'department'   => $e->department->dep_name ?? '—',
                'job'          => $e->jobs_categories->job_name ?? '—',
                'status'       => $e->functional_status,
            ])->values(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  إرسال SMS للموظفين المحددين
    // ─────────────────────────────────────────────
    public function send(Request $request)
    {
        $request->validate([
            'message'      => 'required|string|max:800',
            'employee_ids' => 'required|array|min:1',
        ], [
            'message.required'      => 'نص الرسالة مطلوب',
            'employee_ids.required' => 'يجب اختيار موظف واحد على الأقل',
        ]);

        $comCode = $this->comCode();
        $sms     = new SmsService($comCode);

        if (!$sms->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'خدمة SMS غير مفعّلة. يُرجى الذهاب إلى الضبط العام وإدخال بيانات الاتصال.',
            ], 422);
        }

        $employees = Employee::where('com_code', $comCode)
            ->whereIn('id', $request->employee_ids)
            ->get(['id','employee_name_A','employee_id','emp_mobile']);

        // نجمع أرقام الموظفين اللى عندهم رقم هاتف ونرسلها كلها فى استدعاء API واحد
        // فقط. VLServ يفرض فترة تهدئة بين عمليات إرسال الدفعات على نفس الحساب،
        // فاستدعاء Create مرة لكل موظف كان بيفشل فى كل المحاولات ما عدا الأولى.
        $phonesToSend = [];
        foreach ($employees as $emp) {
            if (!empty($emp->emp_mobile)) {
                $phonesToSend[$emp->id] = $emp->emp_mobile;
            }
        }

        $sendResults = $sms->sendBatch($phonesToSend, $request->message);

        $results = [];

        foreach ($employees as $emp) {
            if (empty($emp->emp_mobile)) {
                $results[] = [
                    'id'     => $emp->id,
                    'name'   => $emp->employee_name_A,
                    'code'   => $emp->employee_id,
                    'phone'  => '—',
                    'status' => 'no_phone',
                    'label'  => 'لا يوجد رقم هاتف',
                ];
                continue;
            }

            $sent = $sendResults[$emp->id]['sent'] ?? false;

            $results[] = [
                'id'     => $emp->id,
                'name'   => $emp->employee_name_A,
                'code'   => $emp->employee_id,
                'phone'  => $emp->emp_mobile,
                'status' => $sent ? 'sent' : 'failed',
                'label'  => $sent ? 'تم الإرسال' : 'فشل الإرسال',
            ];
        }

        $sentCount   = collect($results)->where('status', 'sent')->count();
        $failedCount = collect($results)->where('status', 'failed')->count();
        $noPhone     = collect($results)->where('status', 'no_phone')->count();

        return response()->json([
            'success'     => true,
            'summary'     => [
                'sent'    => $sentCount,
                'failed'  => $failedCount,
                'no_phone'=> $noPhone,
                'total'   => count($results),
            ],
            'results' => $results,
        ]);
    }
}
