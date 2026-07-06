<?php
namespace App\Http\Controllers\Admin\Treasury;

use App\Http\Controllers\Controller;
use App\Models\CashBox;
use App\Models\Branche;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashBoxesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = CashBox::with(['branch', 'glAccount'])->where('com_code', $this->comCode())->orderBy('code')->get();
        return view('admin.treasury.cash_boxes.index', compact('data'));
    }

    public function create()
    {
        $branches = Branche::where('com_code', $this->comCode())->get();
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.treasury.cash_boxes.create', compact('branches', 'accounts'));
    }

    public function store(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:150']);
        $comCode = $this->comCode();

        if (CashBox::where('com_code', $comCode)->where('code', $request->code)->exists()) {
            return back()->withInput()->with('error', 'كود الخزنة مستخدم بالفعل');
        }

        CashBox::create([
            'com_code'        => $comCode,
            'code'            => $request->code,
            'name'            => $request->name,
            'branch_id'       => $request->branch_id ?: null,
            'gl_account_id'   => $request->gl_account_id ?: null,
            'opening_balance' => $request->opening_balance ?? 0,
            'current_balance' => $request->opening_balance ?? 0,
            'is_active'       => (bool) $request->is_active,
        ]);

        return redirect()->route('cash_boxes.index')->with('success', 'تم إضافة الخزنة بنجاح');
    }

    public function edit($id)
    {
        $box      = CashBox::where('com_code', $this->comCode())->findOrFail($id);
        $branches = Branche::where('com_code', $this->comCode())->get();
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.treasury.cash_boxes.edit', compact('box', 'branches', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $box = CashBox::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150']);

        $box->update([
            'name'          => $request->name,
            'branch_id'     => $request->branch_id ?: null,
            'gl_account_id' => $request->gl_account_id ?: null,
            'is_active'     => (bool) $request->is_active,
        ]);

        return redirect()->route('cash_boxes.index')->with('success', 'تم تعديل الخزنة');
    }

    public function delete($id)
    {
        CashBox::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return redirect()->route('cash_boxes.index')->with('success', 'تم حذف الخزنة');
    }
}
