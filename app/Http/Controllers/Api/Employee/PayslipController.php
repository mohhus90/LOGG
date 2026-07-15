<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\MonthlyPayroll;
use App\Services\ArabicPdfService;
use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function index(Request $request)
    {
        $payslips = MonthlyPayroll::where('employee_id', $request->user()->id)
            ->where('status', '>=', 2)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(24);

        return response()->json($payslips);
    }

    public function show(Request $request, int $id)
    {
        $payslip = MonthlyPayroll::where('employee_id', $request->user()->id)
            ->where('status', '>=', 2)
            ->findOrFail($id);

        return response()->json($payslip);
    }

    public function pdf(Request $request, int $id)
    {
        $employee = $request->user();

        $payslip = MonthlyPayroll::where('employee_id', $employee->id)
            ->where('status', '>=', 2)
            ->findOrFail($id);

        $company = Admin_panel_setting::where('com_code', $employee->com_code)->first();

        return ArabicPdfService::download('pdf.payslip', compact('payslip', 'employee', 'company'), 'payslip-' . $payslip->year . '-' . $payslip->month . '.pdf');
    }
}
