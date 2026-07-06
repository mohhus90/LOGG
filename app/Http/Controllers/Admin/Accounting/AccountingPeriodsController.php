<?php
namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AccountingPeriodsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data  = AccountingPeriod::where('com_code', $this->comCode())->orderByDesc('fiscal_year')->orderBy('period_month')->get();
        $years = $data->pluck('fiscal_year')->unique();
        return view('admin.accounting.periods.index', compact('data', 'years'));
    }

    /** إنشاء 12 فترة شهرية لسنة مالية جديدة دفعة واحدة */
    public function generate(Request $request)
    {
        $request->validate(['fiscal_year' => 'required|integer|min:2000|max:2100']);
        $comCode = $this->comCode();
        $year    = (int) $request->fiscal_year;

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();
            AccountingPeriod::updateOrCreate(
                ['com_code' => $comCode, 'fiscal_year' => $year, 'period_month' => $month],
                ['start_date' => $start, 'end_date' => $end]
            );
        }

        return redirect()->route('accounting_periods.index')->with('success', "تم إنشاء فترات السنة المالية $year");
    }

    public function close($id)
    {
        $period = AccountingPeriod::where('com_code', $this->comCode())->findOrFail($id);
        $period->update([
            'is_closed' => true,
            'closed_at' => now(),
            'closed_by' => Auth::guard('admin')->id(),
        ]);
        return back()->with('success', 'تم إغلاق الفترة المحاسبية');
    }

    public function reopen($id)
    {
        $admin = Auth::guard('admin')->user();
        if (!$admin->is_super_admin) {
            abort(403, 'إعادة فتح الفترة متاحة للسوبر أدمن فقط');
        }
        $period = AccountingPeriod::where('com_code', $this->comCode())->findOrFail($id);
        $period->update(['is_closed' => false, 'closed_at' => null, 'closed_by' => null]);
        return back()->with('success', 'تم إعادة فتح الفترة المحاسبية');
    }
}
