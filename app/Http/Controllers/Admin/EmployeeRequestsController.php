<?php
// FILE: app/Http/Controllers/Admin/EmployeeRequestsController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeRequest;
use App\Models\EmployeeVacationBalance;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Admin_panel_setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeRequestsController extends Controller
{
    private function comCode(): int { return Auth::guard('admin')->user()->com_code; }

    // =========================================================
    //  عرض الطلبات الواردة
    // =========================================================
    public function index(Request $request)
    {
        $employees = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();

        $query = EmployeeRequest::with('employee')
            ->where('com_code', $this->comCode());

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('employee_id')) $query->where('employee_id', $request->employee_id);
        if ($request->filled('type'))        $query->where('request_type', $request->type);

        $data = $query->orderByDesc('created_at')->paginate(20);

        $pendingCount = EmployeeRequest::where('com_code', $this->comCode())->where('status', 0)->count();

        return view('admin.employee_requests.index', compact('data', 'employees', 'pendingCount'));
    }

    // =========================================================
    //  قبول الطلب
    // =========================================================
    public function approve(Request $request, int $id)
    {
        $req      = EmployeeRequest::where('com_code', $this->comCode())->findOrFail($id);
        $employee = $req->employee;

        if ($req->status !== 0) {
            return back()->with('error', 'الطلب تمت معالجته مسبقاً');
        }

        DB::beginTransaction();
        try {
            // 1. تحديث حالة الطلب
            $req->update([
                'status'       => 1,
                'reviewed_by'  => Auth::guard('admin')->id(),
                'reviewed_at'  => now(),
                'review_notes' => $request->review_notes,
            ]);

            // 2. تأثير الطلب على الحضور / الرصيد
            if (in_array($req->request_type, ['annual_vacation', 'casual_vacation'])) {
                $this->handleVacationApproval($req, $employee);
            } elseif ($req->request_type === 'late_permission') {
                $this->handleLatePermission($req, $employee);
            } elseif ($req->request_type === 'early_leave') {
                $this->handleEarlyLeavePermission($req, $employee);
            }

            DB::commit();
            return back()->with('success', 'تم قبول الطلب وتطبيق التعديلات تلقائياً');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }
    }

    // =========================================================
    //  رفض الطلب
    // =========================================================
    public function reject(Request $request, int $id)
    {
        $req = EmployeeRequest::where('com_code', $this->comCode())->findOrFail($id);

        if ($req->status !== 0) {
            return back()->with('error', 'الطلب تمت معالجته مسبقاً');
        }

        $req->update([
            'status'       => 2,
            'reviewed_by'  => Auth::guard('admin')->id(),
            'reviewed_at'  => now(),
            'review_notes' => $request->review_notes,
        ]);

        return back()->with('success', 'تم رفض الطلب');
    }

    // ─────────────────────────────────────────────
    //  معالجة قبول الإجازة
    // ─────────────────────────────────────────────
    private function handleVacationApproval(EmployeeRequest $req, Employee $employee): void
    {
        $balance = EmployeeVacationBalance::firstOrCreate(
            ['employee_id' => $employee->id, 'year' => $req->start_date->year],
            ['annual_balance' => 21, 'annual_remaining' => 21, 'casual_balance' => 6, 'casual_remaining' => 6, 'com_code' => $this->comCode()]
        );

        $balance->deductVacation($req->request_type, $req->days_count);

        // تسجيل أيام الإجازة في جدول الحضور
        $type = $req->request_type === 'annual_vacation' ? 3 : 3; // 3=إجازة

        $current = $req->start_date->copy();
        while ($current->lte($req->end_date)) {
            Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'attendance_date' => $current->format('Y-m-d')],
                [
                    'shift_id'        => $employee->shifts_types_id,
                    'status'          => 3, // إجازة
                    'late_minutes'    => 0,
                    'overtime_hours'  => 0,
                    'overtime_amount' => 0,
                    'late_deduction'  => 0,
                    'notes'           => $req->request_type === 'annual_vacation' ? 'إجازة اعتيادية معتمدة' : 'إجازة عارضة معتمدة',
                    'com_code'        => $this->comCode(),
                    'added_by'        => Auth::guard('admin')->id(),
                ]
            );
            $current->addDay();
        }
    }

    // ─────────────────────────────────────────────
    //  معالجة إذن التأخير (يُلغي خصم التأخير)
    // ─────────────────────────────────────────────
    private function handleLatePermission(EmployeeRequest $req, Employee $employee): void
    {
        $att = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $req->start_date->format('Y-m-d'))->first();

        if ($att) {
            $att->update([
                'late_minutes'  => 0,
                'late_deduction'=> 0,
                'notes'         => ($att->notes ?? '') . ' | إذن تأخير معتمد',
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  معالجة إذن الانصراف المبكر
    // ─────────────────────────────────────────────
    private function handleEarlyLeavePermission(EmployeeRequest $req, Employee $employee): void
    {
        $att = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $req->start_date->format('Y-m-d'))->first();

        if ($att) {
            $att->update([
                'notes' => ($att->notes ?? '') . ' | إذن انصراف مبكر معتمد',
            ]);
        }
    }
}
