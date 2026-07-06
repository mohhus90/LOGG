<?php
namespace App\Http\Controllers\Admin\Treasury;

use App\Http\Controllers\Controller;
use App\Models\{CashBox, BankAccount, Cheque, TreasuryVoucher};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TreasuryReportsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $comCode   = $this->comCode();
        $cashBoxes = CashBox::where('com_code', $comCode)->where('is_active', true)->get();
        $banks     = BankAccount::where('com_code', $comCode)->where('is_active', true)->get();
        $totalCash = $cashBoxes->sum('current_balance');
        $totalBank = $banks->sum('current_balance');
        $chequesDue = Cheque::where('com_code', $comCode)->where('status', 'under_collection')
            ->orderBy('due_date')->limit(10)->get();

        return view('admin.treasury.reports.index', compact('cashBoxes', 'banks', 'totalCash', 'totalBank', 'chequesDue'));
    }

    public function chequesDue(Request $request)
    {
        $query = Cheque::where('com_code', $this->comCode())->where('status', 'under_collection');
        if ($request->filled('direction')) $query->where('direction', $request->direction);
        $data = $query->orderBy('due_date')->paginate(30);
        return view('admin.treasury.reports.cheques_due', compact('data'));
    }
}
