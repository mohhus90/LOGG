<?php
namespace App\Http\Controllers\Admin\Treasury;

use App\Http\Controllers\Controller;
use App\Models\{Cheque, BankAccount};
use App\Services\Treasury\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChequesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = Cheque::where('com_code', $this->comCode());
        if ($request->filled('direction')) $query->where('direction', $request->direction);
        if ($request->filled('status'))    $query->where('status', $request->status);
        $data = $query->orderByDesc('due_date')->paginate(20);
        return view('admin.treasury.cheques.index', compact('data'));
    }

    public function show($id)
    {
        $cheque = Cheque::with(['bankAccount', 'treasuryVoucher'])->where('com_code', $this->comCode())->findOrFail($id);
        $banks  = BankAccount::where('com_code', $this->comCode())->where('is_active', true)->get();
        return view('admin.treasury.cheques.show', compact('cheque', 'banks'));
    }

    public function collect(Request $request, $id)
    {
        $cheque = Cheque::where('com_code', $this->comCode())->findOrFail($id);
        if ($request->filled('bank_account_id')) {
            $cheque->update(['bank_account_id' => $request->bank_account_id]);
        }
        try {
            TreasuryService::collectCheque($cheque, Auth::guard('admin')->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'تم تحصيل الشيك بنجاح');
    }

    public function bounce(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);
        $cheque = Cheque::where('com_code', $this->comCode())->findOrFail($id);
        try {
            TreasuryService::bounceCheque($cheque, $request->reason, Auth::guard('admin')->id());
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'تم تسجيل ارتجاع الشيك');
    }
}
