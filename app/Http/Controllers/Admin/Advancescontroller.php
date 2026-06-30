<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Advance;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class AdvancesController extends Controller
{
    private function backToIndex(): \Illuminate\Http\RedirectResponse
    {
        $qs  = session('advances_filters_qs', '');
        $url = route('advances.index') . ($qs ? '?' . $qs : '');
        return redirect($url);
    }

    public function index(Request $request)
    {
        session(['advances_filters_qs' => $request->getQueryString() ?? '']);

        $employees = Employee::orderBy('employee_name_A')->get();
        $query = Advance::with('employee');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->orderByDesc('advance_date')->paginate(20);

        // مجموع السلف المفلترة (كل النتائج لا الصفحة الحالية فقط)
        $filteredTotal = (clone $query)->sum('amount');

        // مجموع سلف الشهر الحالي
        $comCode = Auth::guard('admin')->user()->com_code;
        $monthTotal = Advance::where('com_code', $comCode)
            ->whereYear('advance_date', now()->year)
            ->whereMonth('advance_date', now()->month)
            ->sum('amount');

        return view('admin.advances.index', compact('data', 'employees', 'filteredTotal', 'monthTotal'));
    }

    public function create()
    {
        $employees = Employee::orderBy('employee_name_A')->get();
        return view('admin.advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rows'                       => 'required|array|min:1',
            'rows.*.employee_id'         => 'required|exists:employees,id',
            'rows.*.advance_date'        => 'required|date',
            'rows.*.amount'              => 'required|numeric|min:1',
            'rows.*.installments'        => 'required|integer|min:1',
        ], [
            'rows.required'                    => 'أضف سلفة واحدة على الأقل',
            'rows.*.employee_id.required'      => 'اختر الموظف في كل سطر',
            'rows.*.amount.required'           => 'أدخل قيمة السلفة',
            'rows.*.installments.required'     => 'أدخل عدد الأقساط',
        ]);

        $comCode  = Auth::guard('admin')->user()->com_code;
        $adminId  = Auth::guard('admin')->id();
        $count    = 0;

        foreach ($request->rows as $row) {
            $amount      = $row['amount'];
            $installments = $row['installments'];
            Advance::create([
                'employee_id'         => $row['employee_id'],
                'advance_date'        => $row['advance_date'],
                'amount'              => $amount,
                'installments'        => $installments,
                'monthly_installment' => round($amount / $installments, 2),
                'remaining_amount'    => $amount,
                'status'              => 1,
                'notes'               => $row['notes'] ?? null,
                'com_code'            => $comCode,
                'added_by'            => $adminId,
            ]);
            $count++;
        }

        return $this->backToIndex()->with('success', "تم إضافة {$count} سلفة بنجاح");
    }

    public function edit(int $id)
    {
        $advance   = Advance::findOrFail($id);
        $employees = Employee::orderBy('employee_name_A')->get();
        $qs        = session('advances_filters_qs', '');
        $backUrl   = route('advances.index') . ($qs ? '?' . $qs : '');
        return view('admin.advances.edit', compact('advance', 'employees', 'backUrl'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'installments' => 'required|integer|min:1',
            'status'       => 'required|integer|between:1,3',
        ]);

        $advance = Advance::findOrFail($id);
        $advance->update([
            'installments'         => $request->installments,
            'monthly_installment'  => round($advance->amount / $request->installments, 2),
            'remaining_amount'     => $request->remaining_amount ?? $advance->remaining_amount,
            'status'               => $request->status,
            'notes'                => $request->notes,
            'updated_by'           => Auth::guard('admin')->id(),
        ]);

        return $this->backToIndex()->with('success', 'تم تحديث السلفة بنجاح');
    }

    public function delete(int $id)
    {
        Advance::findOrFail($id)->delete();
        return $this->backToIndex()->with('success', 'تم حذف السلفة بنجاح');
    }

    public function copyMonthForm()
    {
        // الحصول على الأشهر التي يوجد فيها سلف مسجلة
        $months = Advance::where('com_code', Auth::guard('admin')->user()->com_code)
            ->selectRaw("DATE_FORMAT(advance_date, '%Y-%m') as ym, DATE_FORMAT(advance_date, '%m/%Y') as label")
            ->groupBy('ym', 'label')
            ->orderByDesc('ym')
            ->pluck('label', 'ym');

        return view('admin.advances.copy_month', compact('months'));
    }

    public function copyMonth(Request $request)
    {
        $request->validate([
            'source_month' => 'required|date_format:Y-m',
            'target_date'  => 'required|date',
        ], [
            'source_month.required' => 'اختر الشهر المصدر',
            'target_date.required'  => 'اختر تاريخ السلف الجديدة',
        ]);

        $comCode = Auth::guard('admin')->user()->com_code;
        $adminId = Auth::guard('admin')->id();

        [$year, $month] = explode('-', $request->source_month);

        $source = Advance::where('com_code', $comCode)
            ->whereYear('advance_date', $year)
            ->whereMonth('advance_date', $month)
            ->where('status', '!=', 3)
            ->get();

        if ($source->isEmpty()) {
            return back()->with('error', 'لا توجد سلف في الشهر المختار');
        }

        $count = 0;
        foreach ($source as $adv) {
            Advance::create([
                'employee_id'         => $adv->employee_id,
                'advance_date'        => $request->target_date,
                'amount'              => $adv->amount,
                'installments'        => $adv->installments,
                'monthly_installment' => $adv->monthly_installment,
                'remaining_amount'    => $adv->amount,
                'status'              => 1,
                'notes'               => $adv->notes,
                'com_code'            => $comCode,
                'added_by'            => $adminId,
            ]);
            $count++;
        }

        return $this->backToIndex()->with('success', "تم نسخ {$count} سلفة من شهر {$request->source_month} بنجاح");
    }
}
