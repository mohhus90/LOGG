<?php
namespace App\Http\Controllers\Admin\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Branche;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CostCentersController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function index()
    {
        $data = CostCenter::with(['parent', 'branch'])->where('com_code', $this->comCode())->orderBy('code')->paginate(20);
        return view('admin.accounting.cost_centers.index', compact('data'));
    }

    public function create()
    {
        $parents  = CostCenter::where('com_code', $this->comCode())->orderBy('code')->get();
        $branches = Branche::where('com_code', $this->comCode())->get();
        return view('admin.accounting.cost_centers.create', compact('parents', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:150']);
        $comCode = $this->comCode();

        if (CostCenter::where('com_code', $comCode)->where('code', $request->code)->exists()) {
            return back()->withInput()->with('error', 'كود مركز التكلفة مستخدم بالفعل');
        }

        CostCenter::create([
            'com_code'  => $comCode,
            'code'      => $request->code,
            'name'      => $request->name,
            'parent_id' => $request->parent_id ?: null,
            'branch_id' => $request->branch_id ?: null,
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()->route('cost_centers.index')->with('success', 'تم إضافة مركز التكلفة');
    }

    public function edit($id)
    {
        $center   = CostCenter::where('com_code', $this->comCode())->findOrFail($id);
        $parents  = CostCenter::where('com_code', $this->comCode())->where('id', '!=', $id)->orderBy('code')->get();
        $branches = Branche::where('com_code', $this->comCode())->get();
        return view('admin.accounting.cost_centers.edit', compact('center', 'parents', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $center = CostCenter::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:150']);

        $center->update([
            'name'      => $request->name,
            'parent_id' => $request->parent_id ?: null,
            'branch_id' => $request->branch_id ?: null,
            'is_active' => (bool) $request->is_active,
        ]);

        return redirect()->route('cost_centers.index')->with('success', 'تم تعديل مركز التكلفة');
    }

    public function delete($id)
    {
        $center = CostCenter::where('com_code', $this->comCode())->findOrFail($id);
        if ($center->children()->exists()) {
            return back()->with('error', 'لا يمكن حذف مركز تكلفة له مراكز فرعية');
        }
        $center->delete();
        return redirect()->route('cost_centers.index')->with('success', 'تم حذف مركز التكلفة');
    }
}
