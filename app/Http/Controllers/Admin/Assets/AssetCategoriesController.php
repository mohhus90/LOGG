<?php
namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetCategoriesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = AssetCategory::where('com_code', $this->comCode())->orderBy('name')->get();
        return view('admin.assets.categories.index', compact('data'));
    }

    public function create()
    {
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.assets.categories.create', compact('accounts'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:150', 'default_useful_life_years' => 'required|integer|min:1']);

        AssetCategory::create([
            'com_code'                           => $this->comCode(),
            'name'                                => $request->name,
            'default_useful_life_years'           => $request->default_useful_life_years,
            'default_depreciation_method'         => 'straight_line',
            'asset_gl_account_id'                 => $request->asset_gl_account_id ?: null,
            'accum_depreciation_gl_account_id'    => $request->accum_depreciation_gl_account_id ?: null,
            'depreciation_expense_gl_account_id'  => $request->depreciation_expense_gl_account_id ?: null,
            'is_active'                           => (bool) $request->is_active,
        ]);

        return redirect()->route('asset_categories.index')->with('success', 'تم إضافة فئة الأصول بنجاح');
    }

    public function edit($id)
    {
        $category = AssetCategory::where('com_code', $this->comCode())->findOrFail($id);
        $accounts = ChartOfAccount::where('com_code', $this->comCode())->where('is_group', false)->orderBy('account_code')->get();
        return view('admin.assets.categories.edit', compact('category', 'accounts'));
    }

    public function update(Request $request, $id)
    {
        $category = AssetCategory::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150', 'default_useful_life_years' => 'required|integer|min:1']);

        $category->update([
            'name'                                => $request->name,
            'default_useful_life_years'           => $request->default_useful_life_years,
            'asset_gl_account_id'                 => $request->asset_gl_account_id ?: null,
            'accum_depreciation_gl_account_id'    => $request->accum_depreciation_gl_account_id ?: null,
            'depreciation_expense_gl_account_id'  => $request->depreciation_expense_gl_account_id ?: null,
            'is_active'                           => (bool) $request->is_active,
        ]);

        return redirect()->route('asset_categories.index')->with('success', 'تم تعديل فئة الأصول');
    }

    public function delete($id)
    {
        $category = AssetCategory::where('com_code', $this->comCode())->findOrFail($id);
        if ($category->assets()->exists()) {
            return back()->with('error', 'لا يمكن حذف فئة بها أصول مسجلة');
        }
        $category->delete();
        return redirect()->route('asset_categories.index')->with('success', 'تم حذف فئة الأصول');
    }
}
