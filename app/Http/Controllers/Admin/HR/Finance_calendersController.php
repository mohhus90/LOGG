<?php

namespace App\Http\Controllers\Admin\HR;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance_calender;
use App\Models\Month;
use App\Models\Finance_cln_period;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Finance_calendersController extends Controller
{
    // ✅ FIX: مساعد com_code
    private function comCode(): int
    {
        return (int) auth()->guard('admin')->user()->com_code;
    }

    public function index()
    {
        // ✅ FIX: فلترة بـ com_code
        $data = Finance_calender::where('com_code', $this->comCode())
            ->orderBy('finance_yr', 'DESC')
            ->paginate(paginate_counter);

        return view('admin.finance_calender.index', ['data' => $data]);
    }

    public function create()
    {
        return view('admin.finance_calender.create');
    }

    public function store(Request $request, Finance_calender $Finance_calender)
    {
        $request->validate([
            'finance_yr' => [
                'required',
                Rule::unique('finance_calenders')
                    ->where('com_code', $this->comCode()), // ✅ FIX: unique لنفس الشركة فقط
            ],
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ], [
            'finance_yr.required' => 'يجب إدخال السنة المالية',
            'finance_yr.unique'   => 'قد تم إدخال هذه السنة من قبل',
            'start_date.required' => 'يجب إدخال تاريخ بداية السنة المالية',
            'end_date.required'   => 'يجب إدخال تاريخ نهاية السنة المالية',
            'end_date.after'      => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية',
        ]);

        DB::beginTransaction();
        try {
            $adminUser = auth()->guard('admin')->user();

            // ✅ FIX: com_code من الأدمن لا من request
            $createdData = [
                'added_by'   => $adminUser->id,
                'com_code'   => $this->comCode(),
                'finance_yr' => $request->finance_yr,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
            ];

            $flag = Finance_calender::create($createdData);

            if ($flag) {
                $this->generatePeriods($flag->id, $request->start_date, $request->end_date, $adminUser);
            }

            DB::commit();
            return redirect()->route('finance_calender.index')
                ->with('success', 'تم إضافة السنة المالية بنجاح');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Finance Calendar store error: ' . $ex->getMessage());
            return redirect()->back()
                ->with('errorUpdate', 'حدث خطأ: ' . $ex->getMessage())
                ->withInput();
        }
    }

    public function show(string $id) {}

    public function edit($id)
    {
        // ✅ FIX: فلترة بـ com_code
        $data = Finance_calender::where('com_code', $this->comCode())->find($id);

        if (empty($data)) {
            return redirect()->back()->with('error', 'عفوا حدث خطأ');
        }
        return view('admin.finance_calender.update', ['data' => $data]);
    }

    public function updatee(Request $request, string $id)
    {
        // ✅ FIX: فلترة بـ com_code عند التحقق من unique
        $validator = Validator::make($request->all(), [
            'finance_yr' => [
                'required',
                Rule::unique('finance_calenders')
                    ->ignore($id)
                    ->where('com_code', $this->comCode()),
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'قد تم إدخال هذه السنة من قبل')
                ->withInput();
        }

        $request->validate([
            'finance_yr' => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        DB::beginTransaction();
        try {
            // ✅ FIX: فلترة بـ com_code
            $data = Finance_calender::where('com_code', $this->comCode())->find($id);

            if (!$data) {
                return redirect()->back()->with('error', 'عفوا حدث خطأ');
            }

            $adminUser = auth()->guard('admin')->user();
            $is_open   = $request->input('is_open', 0);

            $updatedData = [
                'updated_by' => $adminUser->id,
                // ✅ FIX: لا نحدّث com_code هنا لتجنب null
                'finance_yr' => $request->finance_yr,
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
                'is_open'    => $is_open,
            ];

            Finance_calender::where('id', $id)->update($updatedData);

            // تحديث is_open للأبناء
            Finance_cln_period::where('finance_calenders_id', $id)
                ->update(['is_open' => $is_open]);

            // لو التواريخ تغيرت → أعد توليد الشهور
            if ($data->start_date != $request->start_date || $data->end_date != $request->end_date) {
                Finance_cln_period::where('finance_calenders_id', $id)->delete();
                $this->generatePeriods($id, $request->start_date, $request->end_date, $adminUser, $is_open);
            }

            DB::commit();
            return redirect()->route('finance_calender.index')
                ->with('success', 'تم تحديث السنة المالية بنجاح');

        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('Finance Calendar update error: ' . $ex->getMessage());
            return redirect()->back()
                ->with('errorUpdate', 'حدث خطأ: ' . $ex->getMessage())
                ->withInput();
        }
    }

    public function delete(string $id)
    {
        try {
            // ✅ FIX: فلترة بـ com_code
            $data = Finance_calender::where('com_code', $this->comCode())->find($id);
            if (empty($data)) {
                return redirect()->back()->with('error', 'عفوا حدث خطأ');
            }
            Finance_calender::where('id', $id)->delete();
            return redirect()->route('finance_calender.index')
                ->with('success', 'تم حذف السنة المالية بنجاح');

        } catch (\Exception $ex) {
            return redirect()->back()->with('error', 'عفوا حدث خطأ: ' . $ex->getMessage());
        }
    }

    public function show_year_monthes(Request $request)
    {
        if ($request->ajax()) {
            $finance_cln_periods = Finance_cln_period::where('finance_calenders_id', $request->id)->get();
            return view('admin.finance_calender.show_year_monthes', ['finance_cln_periods' => $finance_cln_periods]);
        }
    }

    // ─────────────────────────────────────────────
    // ✅ مساعد: توليد الشهور لسنة مالية
    // ─────────────────────────────────────────────
    private function generatePeriods(
        int $calendarId,
        string $startDate,
        string $endDate,
        $adminUser,
        int $isOpen = 0
    ): void {
        $start = new DateTime($startDate);
        $end   = new DateTime($endDate);
        $end->modify('+1 day'); // DatePeriod لا يشمل النهاية

        $dateInterval = new DateInterval('P1M');
        $datePeriod   = new DatePeriod($start, $dateInterval, $end);

        foreach ($datePeriod as $date) {
            $monthNameEn = $date->format('F'); // January, February...

            // ✅ FIX: إذا لم يوجد الشهر في جدول monthes - لا نتوقف
            $monthRow = Month::where('monthe_name_en', $monthNameEn)->first();

            $startOfMonth = $date->format('Y-m-01');
            $endOfMonth   = $date->format('Y-m-t');
            $daysCount    = (int)(
                (strtotime($endOfMonth) - strtotime($startOfMonth)) / 86400
            ) + 1;

            $periodData = [
                'finance_calenders_id'    => $calendarId,
                'is_open'                 => $isOpen,
                'month_id'                => $monthRow ? $monthRow->id : null,
                'finance_year'            => (int)$date->format('Y'),
                'start_date'              => $startOfMonth,
                'end_date'                => $endOfMonth,
                'year_of_month'           => $date->format('Y-m'),
                'number_of_days'          => $daysCount,
                'added_by'                => $adminUser->id,
                'updated_by'              => $adminUser->id,
                'start_date_finger_print' => $startOfMonth,
                'end_date_finger_print'   => $endOfMonth,
                'com_code'                => $this->comCode(),
                'created_at'              => now(),
                'updated_at'              => now(),
            ];

            Finance_cln_period::insert($periodData);
        }
    }
}
