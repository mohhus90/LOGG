<?php
// FILE: app/Http/Controllers/Employee/EmployeePortalController.php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin_panel_setting;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeRequest;
use App\Models\EmployeeVacationBalance;
use App\Models\MonthlyPayroll;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class EmployeePortalController extends Controller
{
    private const VACATION_TYPES = ['annual_vacation', 'casual_vacation'];

    private function guard(): Employee
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) abort(redirect()->route('employee.login'));
        return $employee;
    }

    // =========================================================
    //  LOGIN
    // =========================================================
    public function loginForm()
    {
        if (Auth::guard('employee')->check()) {
            return redirect()->route('employee.dashboard');
        }
        return view('employee.login');
    }

    public function loginCheck(Request $request)
    {
        $request->validate([
            'employee_code' => 'required|string',
            'password'      => 'required|string',
        ]);

        $employee = Employee::where('login_username', $request->employee_code)
            ->where('functional_status', 1)
            ->first();

        if (!$employee || !$employee->login_password_hash
            || !Hash::check($request->password, $employee->login_password_hash)) {
            return back()->with('error', 'كود الموظف أو كلمة المرور غير صحيحة')->withInput();
        }

        $request->session()->regenerate();
        Auth::guard('employee')->login($employee);

        return redirect()->route('employee.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employee.login')->with('success', 'تم تسجيل الخروج');
    }

    // =========================================================
    //  DASHBOARD
    // =========================================================
    public function dashboard()
    {
        $employee        = $this->guard();
        $vacationBalance = EmployeeVacationBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)->first();

        $requests = EmployeeRequest::where('employee_id', $employee->id)
            ->orderByDesc('created_at')->take(15)->get();

        $pendingRequests  = $requests->where('status', 0)->count();
        $approvedRequests = EmployeeRequest::where('employee_id', $employee->id)
            ->where('status', 1)
            ->whereMonth('start_date', now()->month)->count();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', now()->toDateString())
            ->first();

        return view('employee.dashboard', compact(
            'employee', 'vacationBalance', 'requests', 'pendingRequests', 'approvedRequests', 'todayAttendance'
        ));
    }

    // =========================================================
    //  إرسال طلب إجازة / إذن
    // =========================================================
    public function storeRequest(Request $request)
    {
        $employee = $this->guard();

        $request->validate([
            'request_type' => 'required|in:annual_vacation,casual_vacation,late_permission,early_leave,mission',
            'reason'       => 'nullable|string|max:500',
        ]);

        $isPermission = in_array($request->request_type, ['late_permission', 'early_leave']);

        if ($isPermission) {
            $startDate  = $request->perm_date ?? today()->format('Y-m-d');
            $endDate    = $startDate;
            $daysCount  = 0;
            $timeFrom   = $request->time_from;
            $timeTo     = $request->time_to;
        } else {
            $request->validate([
                'start_date' => 'required|date|after_or_equal:today',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);
            $startDate = $request->start_date;
            $endDate   = $request->end_date;
            $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
            $timeFrom  = null;
            $timeTo    = null;
        }

        if (in_array($request->request_type, self::VACATION_TYPES)) {
            $balance = EmployeeVacationBalance::where('employee_id', $employee->id)
                ->where('year', now()->year)->first();

            if ($balance) {
                $remaining = $request->request_type === 'annual_vacation'
                    ? $balance->annual_remaining
                    : $balance->casual_remaining;

                if ($daysCount > $remaining) {
                    return back()->with('error',
                        "لا يوجد رصيد كافٍ. الرصيد المتاح: $remaining يوم فقط."
                    );
                }
            }
        }

        EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'request_type' => $request->request_type,
            'request_date' => today(),
            'start_date'   => $startDate,
            'end_date'     => $endDate,
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'days_count'   => $daysCount,
            'reason'       => $request->reason,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return back()->with('success', 'تم إرسال طلبك بنجاح. في انتظار موافقة المدير.');
    }

    public function cancelRequest(int $id)
    {
        $employee = $this->guard();

        $leaveRequest = EmployeeRequest::where('employee_id', $employee->id)->findOrFail($id);

        if ($leaveRequest->status !== 0) {
            return back()->with('error', 'لا يمكن إلغاء طلب تمت معالجته مسبقاً');
        }

        $leaveRequest->update(['status' => 3]);

        return back()->with('success', 'تم إلغاء الطلب');
    }

    // =========================================================
    //  قسائم الراتب
    // =========================================================
    public function payslips()
    {
        $employee = $this->guard();

        $payslips = MonthlyPayroll::where('employee_id', $employee->id)
            ->where('status', '>=', 2)
            ->orderByDesc('year')->orderByDesc('month')
            ->get();

        return view('employee.payslips', compact('employee', 'payslips'));
    }

    public function payslipPdf(int $id)
    {
        $employee = $this->guard();

        $payslip = MonthlyPayroll::where('employee_id', $employee->id)
            ->where('status', '>=', 2)
            ->findOrFail($id);

        $company = Admin_panel_setting::where('com_code', $employee->com_code)->first();

        $pdf = Pdf::loadView('pdf.payslip', compact('payslip', 'employee', 'company'));

        return $pdf->download('payslip-' . $payslip->year . '-' . $payslip->month . '.pdf');
    }

    // =========================================================
    //  شهادة الراتب (خطاب HR)
    // =========================================================
    private function latestCertificateRequest(int $employeeId): ?EmployeeRequest
    {
        return EmployeeRequest::where('employee_id', $employeeId)
            ->where('request_type', 'salary_certificate')
            ->orderByDesc('created_at')
            ->first();
    }

    public function salaryCertificatePage()
    {
        $employee = $this->guard();
        $latest   = $this->latestCertificateRequest($employee->id);

        return view('employee.salary_certificate', compact('employee', 'latest'));
    }

    public function salaryCertificateRequestAccess(Request $request)
    {
        $employee = $this->guard();

        $request->validate(['reason' => 'required|string|max:1000'], [
            'reason.required' => 'من فضلك اذكر سبب طلب شهادة الراتب',
        ]);

        if ($this->latestCertificateRequest($employee->id)?->status === 0) {
            return back()->with('error', 'يوجد طلب شهادة راتب قيد الانتظار بالفعل');
        }

        EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'request_type' => 'salary_certificate',
            'request_date' => now()->toDateString(),
            'start_date'   => now()->toDateString(),
            'days_count'   => 0,
            'reason'       => $request->reason,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return back()->with('success', 'تم إرسال الطلب، بانتظار الموافقة');
    }

    public function salaryCertificate()
    {
        $employee = $this->guard();

        if ($this->latestCertificateRequest($employee->id)?->status !== 1) {
            return back()->with('error', 'يجب طلب شهادة الراتب وذكر السبب والحصول على موافقة قبل التنزيل');
        }

        $company = Admin_panel_setting::where('com_code', $employee->com_code)->first();

        $pdf = Pdf::loadView('pdf.salary_certificate', compact('employee', 'company'));

        return $pdf->download('salary-certificate-' . $employee->id . '.pdf');
    }

    // =========================================================
    //  المستندات الشخصية
    // =========================================================
    public function documents()
    {
        $employee  = $this->guard();
        $documents = EmployeeDocument::where('employee_id', $employee->id)
            ->orderByDesc('created_at')->get();

        return view('employee.documents', compact('employee', 'documents'));
    }

    public function documentRequestAccess(int $id)
    {
        $employee = $this->guard();
        $document = EmployeeDocument::where('employee_id', $employee->id)->findOrFail($id);

        if ($document->latestAccessRequest()?->status === 0) {
            return back()->with('error', 'يوجد طلب وصول قيد الانتظار لهذا المستند بالفعل');
        }

        EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'document_id'  => $document->id,
            'request_type' => 'document_download',
            'request_date' => now()->toDateString(),
            'start_date'   => now()->toDateString(),
            'days_count'   => 0,
            'reason'       => 'طلب الوصول لتنزيل مستند: ' . $document->type_label,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return back()->with('success', 'تم إرسال طلب الوصول، بانتظار الموافقة');
    }

    public function documentDownload(int $id)
    {
        $employee = $this->guard();
        $document = EmployeeDocument::where('employee_id', $employee->id)->findOrFail($id);

        if (!$document->isApprovedForDownload()) {
            return back()->with('error', 'يجب طلب الوصول لهذا المستند والحصول على موافقة قبل التنزيل');
        }

        $path = public_path($document->doc_path);
        if (!file_exists($path)) {
            return back()->with('error', 'الملف غير موجود');
        }

        return response()->download($path, $document->doc_original_name);
    }

    // =========================================================
    //  طلب استقالة
    // =========================================================
    public function resignationForm()
    {
        $employee = $this->guard();

        $resignation = EmployeeRequest::where('employee_id', $employee->id)
            ->where('request_type', 'resignation')
            ->orderByDesc('created_at')->first();

        return view('employee.resignation', compact('employee', 'resignation'));
    }

    public function resignationStore(Request $request)
    {
        $employee = $this->guard();

        $request->validate([
            'last_working_date' => 'required|date|after_or_equal:today',
            'reason'            => 'nullable|string|max:1000',
        ]);

        $existing = EmployeeRequest::where('employee_id', $employee->id)
            ->where('request_type', 'resignation')
            ->where('status', 0)
            ->first();

        if ($existing) {
            return back()->with('error', 'لديك طلب استقالة قيد الانتظار بالفعل');
        }

        EmployeeRequest::create([
            'employee_id'  => $employee->id,
            'request_type' => 'resignation',
            'request_date' => now()->toDateString(),
            'start_date'   => $request->last_working_date,
            'days_count'   => 1,
            'reason'       => $request->reason,
            'status'       => 0,
            'com_code'     => $employee->com_code,
        ]);

        return redirect()->route('employee.resignation')->with('success', 'تم إرسال طلب الاستقالة بنجاح');
    }

    // =========================================================
    //  سجل الحضور والانصراف
    // =========================================================
    public function attendanceHistory(Request $request)
    {
        $employee = $this->guard();

        $month = (int) ($request->month ?: now()->month);
        $year  = (int) ($request->year ?: now()->year);

        $attendances = Attendance::with('shift')
            ->where('employee_id', $employee->id)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year)
            ->orderByDesc('attendance_date')
            ->get();

        return view('employee.attendance', compact('employee', 'attendances', 'month', 'year'));
    }
}
