<?php

namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\Finance_cln_period;
use App\Models\Month;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Finance_cln_periodsController extends Controller
{
    private function comCode(): int
    {
        return (int) auth()->guard('admin')->user()->com_code;
    }

    public function edit(int $id)
    {
        $period = Finance_cln_period::where('com_code', $this->comCode())->findOrFail($id);
        $months = Month::orderBy('id')->get();
        return view('admin.finance_calender.edit_period', compact('period', 'months'));
    }

    public function update(Request $request, int $id)
    {
        $request->validate([
            'start_date'              => 'required|date',
            'end_date'                => 'required|date|after_or_equal:start_date',
            'start_date_finger_print' => 'required|date',
            'end_date_finger_print'   => 'required|date|after_or_equal:start_date_finger_print',
            'number_of_days'          => 'required|integer|min:1|max:31',
            'is_open'                 => 'nullable|in:0,1',
            'working_days'            => 'nullable|integer|min:0|max:31',
            'vacation_days_accrual'   => 'nullable|numeric|min:0',
        ], [
            'start_date.required'              => 'تاريخ البداية مطلوب',
            'end_date.required'                => 'تاريخ النهاية مطلوب',
            'end_date.after_or_equal'          => 'تاريخ النهاية يجب أن يكون بعد البداية',
            'start_date_finger_print.required' => 'تاريخ بداية البصمة مطلوب',
            'end_date_finger_print.required'   => 'تاريخ نهاية البصمة مطلوب',
            'number_of_days.required'          => 'عدد أيام الشهر مطلوب',
        ]);

        DB::beginTransaction();
        try {
            $period = Finance_cln_period::where('com_code', $this->comCode())->findOrFail($id);

            $data = [
                'start_date'              => $request->start_date,
                'end_date'                => $request->end_date,
                'start_date_finger_print' => $request->start_date_finger_print,
                'end_date_finger_print'   => $request->end_date_finger_print,
                'number_of_days'          => $request->number_of_days,
                'is_open'                 => $request->input('is_open', 0),
                'updated_by'              => Auth::guard('admin')->id(),
                'updated_at'              => now(),
            ];

            // حقول السنة المالية المرتبطة بالحسابات (إن وجدت الأعمدة)
            if ($request->filled('working_days')) {
                $data['working_days'] = $request->working_days;
            }
            if ($request->filled('vacation_days_accrual')) {
                $data['vacation_days_accrual'] = $request->vacation_days_accrual;
            }

            Finance_cln_period::where('id', $id)->update($data);

            DB::commit();
            return redirect()
                ->route('finance_calender.index')
                ->with('success', 'تم تحديث الشهر المالي بنجاح');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Finance_cln_periodsController@update: ' . $ex->getMessage());
            return redirect()->back()
                ->with('errorUpdate', 'حدث خطأ أثناء التحديث')
                ->withInput();
        }
    }
}
