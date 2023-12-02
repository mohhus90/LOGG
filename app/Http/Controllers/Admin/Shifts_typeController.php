<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shifts_type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Shifts_typeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Shifts_type::select('*')->orderby('id','DESC')->paginate(paginate_counter);

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
        DB::beginTransaction();
        $request->validate([
            'type'=>'required',
            'from_time'=>'required',
            'to_time'=>'required',
          

        ],[
            'type.required'=>'يجب ادخال نوع الشيفت',
            'from_time.required'=>'يجب ادخال بداية الشيفت',
            'to_time.required'=>'يجب ادخال نهاية الشيفت'
            
        ]);

        try{
            $datainsert['com_code']=auth()->guard('admin')->user()->com_code;
            $datainsert['type']=$request->type;
            $datainsert['from_time']=$request->from_time;
            $datainsert['to_time']=$request->to_time;
            $checkIfExist=get_cols_where_row(new shifts_type(),array("id"),$datainsert);
            if(!empty($checkIfExist)){
                return redirect()->back()->with(['error'=>'هذا الشيفت تم تسجيله من قبل'])->withInput();
            }
            $datainsert['added_by'] = auth()->guard('admin')->user()->id;
            $datainsert['total_hour']=$request->total_hour;
            Shifts_type::insert($datainsert);
            DB::commit();
            return redirect()->route('shifts.index')->with(['success'=>'تم الحفظ بنجاح']);
        }catch(\Exception $ex){
            DB::rollBack();
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '. $ex->getMessage()])->withInput();
        }
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
