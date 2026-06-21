<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EtaFreeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EtaFreeZoneController extends Controller
{
    private function comCode(): int
    {
        return (int) Auth::guard('admin')->user()->com_code;
    }

    public function index()
    {
        $zones = EtaFreeZone::where('com_code', $this->comCode())
            ->orderBy('name')
            ->get();

        return view('admin.tax.free_zones', compact('zones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tax_id' => 'required|digits_between:9,15',
            'name'   => 'nullable|string|max:200',
        ]);

        $comCode = $this->comCode();

        $exists = EtaFreeZone::where('com_code', $comCode)
            ->where('tax_id', $request->tax_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'رقم التسجيل هذا موجود مسبقاً.');
        }

        EtaFreeZone::create([
            'com_code' => $comCode,
            'tax_id'   => $request->tax_id,
            'name'     => $request->name,
        ]);

        return back()->with('success', 'تمت الإضافة بنجاح.');
    }

    public function destroy(EtaFreeZone $freeZone)
    {
        abort_if($freeZone->com_code !== $this->comCode(), 403);
        $freeZone->delete();
        return back()->with('success', 'تم الحذف.');
    }
}
