<?php
// FILE: app/Http/Controllers/Employee/EmployeePortalController.php
namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeRequest;
use App\Models\EmployeeVacationBalance;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class EmployeePortalController extends Controller
{
    private function guard(): Employee
    {
        $id = session('employee_portal_id');
        if (!$id) abort(redirect()->route('employee.login'));
        return Employee::findOrFail($id);
    }

    // =========================================================
    //  LOGIN
    // =========================================================
    public function loginForm()
    {
        if (session('employee_portal_id')) {
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

        $employee = Employee::where('employee_id', $request->employee_code)->first();

        if (!$employee) {
            return back()->with('error', 'كود الموظف غير موجود')->withInput();
        }

        // كلمة المرور الافتراضية = رقم الهاتف
        $defaultPassword = $employee->phone ?? $employee->employee_id;
        $password        = $employee->password ?? $defaultPassword;

        $valid = Hash::check($request->password, $password)
            || $request->password === $password;

        if (!$valid) {
            return back()->with('error', 'كلمة المرور غير صحيحة')->withInput();
        }

        session(['employee_portal_id' => $employee->id]);
        return redirect()->route('employee.dashboard');
    }

    public function logout()
    {
        session()->forget('employee_portal_id');
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

        return view('employee.dashboard', compact(
            'employee', 'vacationBalance', 'requests', 'pendingRequests', 'approvedRequests'
        ));
    }

    // =========================================================
    //  إرسال طلب
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

        // التحقق من رصيد الإجازة
        if (in_array($request->request_type, ['annual_vacation', 'casual_vacation'])) {
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
}
