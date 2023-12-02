<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shifts_type;
use Illuminate\Http\Request;

class Shifts_typeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Shifts_type::select('*')->orderby('finance_yr','DESC')->paginate(paginate_counter);

        return view('admin.shifts.index',['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.shifts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'type'=>'required',
            'from_time'=>'required',
            'to_time'=>'required',
          

        ],[
            'type.required'=>'يجب ادخال نوع الشيفت',
            'from_time.required'=>'يجب ادخال بداية الشيفت',
            'to_time.required'=>'يجب ادخال نهاية الشيفت'
            
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Shifts_type $shifts_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shifts_type $shifts_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shifts_type $shifts_type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shifts_type $shifts_type)
    {
        //
    }
}
