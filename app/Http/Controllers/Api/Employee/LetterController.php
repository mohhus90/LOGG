<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LetterController extends Controller
{
    public function salaryCertificate(Request $request)
    {
        $employee = $request->user();
        $company  = Admin_panel_setting::where('com_code', $employee->com_code)->first();

        $pdf = Pdf::loadView('pdf.salary_certificate', compact('employee', 'company'));

        return $pdf->download('salary-certificate-' . $employee->id . '.pdf');
    }
}
