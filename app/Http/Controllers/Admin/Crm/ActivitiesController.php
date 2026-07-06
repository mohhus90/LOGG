<?php
namespace App\Http\Controllers\Admin\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivitiesController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    public function store(Request $request)
    {
        $request->validate([
            'linked_type'   => 'required|in:lead,customer,opportunity',
            'linked_id'     => 'required|integer',
            'type'          => 'required|in:call,meeting,note',
            'notes'         => 'required|string',
            'activity_date' => 'required|date',
        ]);

        CrmActivity::create([
            'com_code'      => $this->comCode(),
            'linked_type'   => $request->linked_type,
            'linked_id'     => $request->linked_id,
            'type'          => $request->type,
            'notes'         => $request->notes,
            'activity_date' => $request->activity_date,
            'created_by'    => Auth::guard('admin')->id(),
        ]);

        return back()->with('success', 'تم تسجيل المتابعة بنجاح');
    }

    public function delete($id)
    {
        CrmActivity::where('com_code', $this->comCode())->findOrFail($id)->delete();
        return back()->with('success', 'تم حذف المتابعة');
    }
}
