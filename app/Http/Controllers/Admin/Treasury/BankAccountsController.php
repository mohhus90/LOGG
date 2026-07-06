<?php
namespace App\Http\Controllers\Admin\Treasury;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Branche;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankAccountsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = BankAccount::with(['branch', 'glAccount'])->where('com_code', $this->comCode())->orderBy('bank_name')->get();
        return view('admin.treasury.bank_accounts.index', compact('data'));
    }

    public function create()
    {
        $branches = Branche::where('com_code', $this->comCode())->get();
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.treasury.bank_accounts.create', compact('branches', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate(['bank_name' => 'required|string|max:150', 'account_name' => 'required|string|max:150']);
        $comCode = $this->comCode();

        BankAccount::create([
            'com_code'        => $comCode,
            'bank_name'       => $request->bank_name,
            'account_name'    => $request->account_name,
            'account_number'  => $request->account_number,
            'iban'            => $request->iban,
            'swift_code'      => $request->swift_code,
            'branch_id'       => $request->branch_id ?: null,
            'gl_account_id'   => $request->gl_account_id ?: null,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
            'is_active'       => (bool) $request->is_active,
        ]);

        return redirect()->route('bank_accounts.index')->with('success', 'تم إضافة الحساب البنكي بنجاح');
    }

    public function edit($id)
    {
        $account  = BankAccount::where('com_code', $this->comCode())->findOrFail($id);
        $branches = Branche::where('com_code', $this->comCode())->get();
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.treasury.bank_accounts.edit', compact('account', 'branches', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $bankAccount = BankAccount::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['bank_name' => 'required|string|max:150', 'account_name' => 'required|string|max:150']);

        $bankAccount->update([
            'bank_name'      => $request->bank_name,
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'iban'           => $request->iban,
            'swift_code'     => $request->swift_code,
            'branch_id'      => $request->branch_id ?: null,
            'gl_account_id'  => $request->gl_account_id ?: null,
            'is_active'      => (bool) $request->is_active,
        ]);

        return redirect()->route('bank_accounts.index')->with('success', 'تم تعديل الحساب البنكي');
    }

    public function delete($id)
    {
        BankAccount::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('bank_accounts.index')->with('success', 'تم حذف الحساب البنكي');
    }
}
