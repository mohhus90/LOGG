<?php
namespace App\Http\Controllers\Admin\HR;

use App\Http\Controllers\Controller;
use App\Models\Admin_panel_setting;
use App\Models\IncomeTaxBracket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeTaxBracketsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $brackets = IncomeTaxBracket::where('com_code', $this->comCode())->orderBy('from_amount')->get();
        $setting  = Admin_panel_setting::where('com_code', $this->comCode())->first();
        return view('admin.income_tax.index', compact('brackets', 'setting'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_amount' => 'required|numeric|min:0',
            'to_amount'   => 'nullable|numeric|gt:from_amount',
            'rate'        => 'required|numeric|min:0|max:100',
        ]);

        IncomeTaxBracket::create([
            'com_code'    => $this->comCode(),
            'from_amount' => $request->from_amount,
            'to_amount'   => $request->to_amount ?: null,
            'rate'        => $request->rate,
            'is_active'   => true,
        ]);

        return back()->with('success', 'تمت إضافة الشريحة بنجاح');
    }

    public function destroy($id)
    {
        IncomeTaxBracket::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return back()->with('success', 'تم حذف الشريحة');
    }

    public function updateExemption(Request $request)
    {
        $request->validate(['income_tax_exemption_monthly' => 'required|numeric|min:0']);

        Admin_panel_setting::where('com_code', $this->comCode())
            ->update(['income_tax_exemption_monthly' => $request->income_tax_exemption_monthly]);

        return back()->with('success', 'تم تحديث الإعفاء الضريبي الشهري');
    }
}
