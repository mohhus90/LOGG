<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advance;
use App\Models\Employee;
use App\Services\SmsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancesController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public function index(Request $request)
    {
        $comCode   = $this->comCode();
        $employees = Employee::where('com_code', $comCode)->orderBy('employee_name_A')->get();
        $query     = Advance::with('employee')->where('com_code', $comCode);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $query->whereMonth('advance_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('advance_date', $request->year);
        }

        $filteredTotal = (clone $query)->sum('amount');

        $monthTotal = Advance::where('com_code', $comCode)
            ->whereMonth('advance_date', now()->month)
            ->whereYear('advance_date', now()->year)
            ->sum('amount');

        $data = $query->orderByDesc('advance_date')->paginate(20);
        return view('admin.advances.index', compact('data', 'employees', 'filteredTotal', 'monthTotal'));
    }

    public function create()
    {
        $employees = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        return view('admin.advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rows'                  => 'required|array|min:1',
            'rows.*.employee_id'    => 'required|exists:employees,id',
            'rows.*.advance_date'   => 'required|date',
            'rows.*.amount'         => 'required|numeric|min:1',
            'rows.*.installments'   => 'required|integer|min:1',
        ], [
            'rows.required'                 => 'أضف سلفة واحدة على الأقل',
            'rows.*.employee_id.required'   => 'اختر الموظف',
            'rows.*.advance_date.required'  => 'أدخل تاريخ السلفة',
            'rows.*.amount.required'        => 'أدخل قيمة السلفة',
            'rows.*.installments.required'  => 'أدخل عدد الأقساط',
        ]);

        $comCode = $this->comCode();
        $admin   = Auth::guard('admin')->user();
        $count   = 0;

        DB::beginTransaction();
        try {
            foreach ($request->rows as $row) {
                Advance::create([
                    'employee_id'          => $row['employee_id'],
                    'advance_date'         => $row['advance_date'],
                    'amount'               => $row['amount'],
                    'installments'         => $row['installments'],
                    'monthly_installment'  => round($row['amount'] / $row['installments'], 2),
                    'remaining_amount'     => $row['amount'],
                    'status'               => 1,
                    'notes'                => $row['notes'] ?? null,
                    'com_code'             => $comCode,
                    'added_by'             => $admin->id,
                ]);
                $count++;

                // SMS إشعار السلفة
                $employee = Employee::find($row['employee_id']);
                if ($employee && $employee->emp_mobile) {
                    try {
                        (new SmsService($comCode))
                            ->sendAdvanceCreated($employee->emp_mobile, $employee->employee_name_A, (float)$row['amount']);
                    } catch (\Exception $e) {
                        Log::warning('SMS advance failed: ' . $e->getMessage());
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'خطأ: ' . $e->getMessage());
        }

        return redirect()->route('advances.index')->with('success', "تم إضافة {$count} سلفة بنجاح");
    }

    public function edit(int $id)
    {
        $advance   = Advance::where('com_code', $this->comCode())->findOrFail($id);
        $employees = Employee::where('com_code', $this->comCode())->orderBy('employee_name_A')->get();
        return view('admin.advances.edit', compact('advance', 'employees'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'installments' => 'required|integer|min:1',
            'status'       => 'required|integer|between:1,3',
        ]);

        $advance = Advance::where('com_code', $this->comCode())->findOrFail($id);
        $advance->update([
            'installments'         => $request->installments,
            'monthly_installment'  => round($advance->amount / $request->installments, 2),
            'remaining_amount'     => $request->remaining_amount ?? $advance->remaining_amount,
            'status'               => $request->status,
            'notes'                => $request->notes,
            'updated_by'           => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('advances.index')->with('success', 'تم تحديث السلفة بنجاح');
    }

    public function delete(int $id)
    {
        Advance::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('advances.index')->with('success', 'تم حذف السلفة بنجاح');
    }

    // ─────────────────────────────────────────────
    //  نسخ السلف من شهر سابق
    // ─────────────────────────────────────────────
    public function copyMonthForm()
    {
        $months = Advance::where('com_code', $this->comCode())
            ->selectRaw("DATE_FORMAT(advance_date, '%Y-%m') as ym")
            ->distinct()
            ->orderByDesc('ym')
            ->pluck('ym')
            ->mapWithKeys(fn($ym) => [$ym => Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y')]);

        return view('admin.advances.copy_month', compact('months'));
    }

    public function copyMonth(Request $request)
    {
        $request->validate([
            'source_month' => 'required|regex:/^\d{4}-\d{2}$/',
            'target_date'  => 'required|date',
        ], [
            'source_month.required' => 'اختر الشهر المصدر',
            'target_date.required'  => 'أدخل تاريخ السلف الجديدة',
        ]);

        $comCode = $this->comCode();
        [$year, $month] = explode('-', $request->source_month);

        $sourceAdvances = Advance::where('com_code', $comCode)
            ->whereYear('advance_date', $year)
            ->whereMonth('advance_date', $month)
            ->where('status', '!=', 3)
            ->get();

        if ($sourceAdvances->isEmpty()) {
            return back()->with('error', 'لا توجد سلف في الشهر المختار لنسخها');
        }

        $admin = Auth::guard('admin')->user();
        $count = 0;

        DB::beginTransaction();
        try {
            foreach ($sourceAdvances as $source) {
                Advance::create([
                    'employee_id'         => $source->employee_id,
                    'advance_date'        => $request->target_date,
                    'amount'              => $source->amount,
                    'installments'        => $source->installments,
                    'monthly_installment' => $source->monthly_installment,
                    'remaining_amount'    => $source->amount,
                    'status'              => 1,
                    'notes'               => $source->notes,
                    'com_code'            => $comCode,
                    'added_by'            => $admin->id,
                ]);
                $count++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'خطأ: ' . $e->getMessage());
        }

        return redirect()->route('advances.index')->with('success', "تم نسخ {$count} سلفة بنجاح");
    }
}
