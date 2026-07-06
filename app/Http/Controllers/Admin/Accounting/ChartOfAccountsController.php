<?php
namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->orderBy('account_code')->get();
        $tree     = $this->buildTree($accounts);
        return view('admin.accounting.accounts.index', compact('accounts', 'tree'));
    }

    private function buildTree($accounts, $parentId = null, $depth = 0): array
    {
        $branch = [];
        foreach ($accounts->where('parent_id', $parentId) as $account) {
            $branch[] = ['account' => $account, 'depth' => $depth];
            $branch = array_merge($branch, $this->buildTree($accounts, $account->id, $depth + 1));
        }
        return $branch;
    }

    public function create()
    {
        $groups = ChartOfAccount::where('com_code', $this->comCode())->orderBy('account_code')->get();
        return view('admin.accounting.accounts.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_code' => 'required|string|max:20',
            'account_name' => 'required|string|max:150',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
        ]);

        $comCode = $this->comCode();
        $exists = ChartOfAccount::where('com_code', $comCode)->where('account_code', $request->account_code)->exists();
        if ($exists) {
            return back()->withInput()->with('error', 'رقم الحساب مستخدم بالفعل');
        }

        $parent = $request->filled('parent_id') ? ChartOfAccount::where('com_code', $comCode)->find($request->parent_id) : null;

        ChartOfAccount::create([
            'com_code'          => $comCode,
            'account_code'      => $request->account_code,
            'account_name'      => $request->account_name,
            'account_name_en'   => $request->account_name_en,
            'account_type'      => $request->account_type,
            'account_nature'    => $request->account_nature ?? (in_array($request->account_type, ['asset', 'expense']) ? 'debit' : 'credit'),
            'parent_id'         => $parent?->id,
            'level'             => $parent ? $parent->level + 1 : 1,
            'is_group'          => (bool) $request->is_group,
            'is_active'         => (bool) $request->is_active,
            'allow_cost_center' => (bool) $request->allow_cost_center,
            'opening_balance'   => $request->opening_balance ?? 0,
            'current_balance'   => $request->opening_balance ?? 0,
            'notes'             => $request->notes,
        ]);

        return redirect()->route('chart_of_accounts.index')->with('success', 'تم إضافة الحساب بنجاح');
    }

    public function edit($id)
    {
        $account = ChartOfAccount::where('com_code', $this->comCode())->findOrFail($id);
        $groups  = ChartOfAccount::where('com_code', $this->comCode())->where('id', '!=', $id)->orderBy('account_code')->get();
        return view('admin.accounting.accounts.edit', compact('account', 'groups'));
    }

    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate([
            'account_name' => 'required|string|max:150',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
        ]);

        $parent = $request->filled('parent_id') ? ChartOfAccount::where('com_code', $this->comCode())->find($request->parent_id) : null;

        $account->update([
            'account_name'      => $request->account_name,
            'account_name_en'   => $request->account_name_en,
            'account_type'      => $request->account_type,
            'account_nature'    => $request->account_nature ?? $account->account_nature,
            'parent_id'         => $parent?->id,
            'level'             => $parent ? $parent->level + 1 : 1,
            'is_group'          => (bool) $request->is_group,
            'is_active'         => (bool) $request->is_active,
            'allow_cost_center' => (bool) $request->allow_cost_center,
            'notes'             => $request->notes,
        ]);

        return redirect()->route('chart_of_accounts.index')->with('success', 'تم تعديل الحساب');
    }

    public function delete($id)
    {
        $account = ChartOfAccount::where('com_code', $this->comCode())->findOrFail($id);

        if ($account->children()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب له حسابات فرعية');
        }
        if ($account->lines()->exists()) {
            return back()->with('error', 'لا يمكن حذف حساب لديه حركة قيود مرحّلة');
        }

        $account->delete();
        return redirect()->route('chart_of_accounts.index')->with('success', 'تم حذف الحساب');
    }
}
