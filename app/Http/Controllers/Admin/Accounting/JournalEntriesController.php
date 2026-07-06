<?php
namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalEntriesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index(Request $request)
    {
        $query = JournalEntry::where('com_code', $this->comCode());
        if ($request->filled('status'))        $query->where('status', $request->status);
        if ($request->filled('source_module'))  $query->where('source_module', $request->source_module);
        if ($request->filled('from'))           $query->whereDate('entry_date', '>=', $request->from);
        if ($request->filled('to'))             $query->whereDate('entry_date', '<=', $request->to);

        $data = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(20);
        return view('admin.accounting.journal_entries.index', compact('data'));
    }

    public function create()
    {
        $accounts     = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        $costCenters  = CostCenter::where('com_code', $this->comCode())->where('is_active', true)->orderBy('code')->get();
        return view('admin.accounting.journal_entries.create', compact('accounts', 'costCenters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'lines'      => 'required|array|min:2',
        ]);

        $lines = [];
        foreach ($request->lines as $row) {
            $debit  = (float) ($row['debit'] ?? 0);
            $credit = (float) ($row['credit'] ?? 0);
            if (empty($row['account_id']) || ($debit == 0 && $credit == 0)) continue;
            $lines[] = [
                'account_id'     => $row['account_id'],
                'cost_center_id' => $row['cost_center_id'] ?? null,
                'debit'          => $debit,
                'credit'         => $credit,
                'description'    => $row['description'] ?? null,
            ];
        }

        if (count($lines) < 2) {
            return back()->withInput()->with('error', 'يجب إدخال سطرين على الأقل بقيمة مدين/دائن');
        }

        try {
            $entry = JournalPostingService::post('manual', $this->comCode(), $lines, [
                'entry_date'  => $request->entry_date,
                'reference'   => $request->reference,
                'description' => $request->description,
                'created_by'  => Auth::guard('admin')->id(),
            ]);
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('journal_entries.show', $entry->id)->with('success', 'تم إنشاء القيد وترحيله بنجاح');
    }

    public function show($id)
    {
        $entry = JournalEntry::with(['lines.account', 'lines.costCenter', 'createdBy', 'reversedEntry'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.accounting.journal_entries.show', compact('entry'));
    }

    public function reverse(Request $request, $id)
    {
        $entry = JournalEntry::where('com_code', $this->comCode())->findOrFail($id);
        try {
            $reversal = JournalPostingService::reverse($entry, Auth::guard('admin')->id(), $request->reason);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
        return redirect()->route('journal_entries.show', $reversal->id)->with('success', 'تم عكس القيد بنجاح');
    }
}
