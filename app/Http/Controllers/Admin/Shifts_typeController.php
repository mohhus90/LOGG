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
            $datainsert['created_at']=date('Y-m-d H:i:s');
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
    public function edit($id)
    {
        $data=Shifts_type::select('*')->where(['id'=>$id])->first();
        if(empty($data)){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
        }else{
            return view('admin.shifts.update',['data'=>$data]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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
            $data=Shifts_type::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            $dataupdate['com_code']=auth()->guard('admin')->user()->com_code;
            $dataupdate['type']=$request->type;
            $dataupdate['from_time']=$request->from_time;
            $dataupdate['to_time']=$request->to_time;
            $checkIfExist=get_cols_where_row(new shifts_type(),array("id"),$dataupdate);
            if(!empty($checkIfExist)){
                return redirect()->back()->with(['error'=>'هذا الشيفت تم تسجيله من قبل'])->withInput();
            }
            $dataupdate['updated_by'] = auth()->guard('admin')->user()->id;
            $dataupdate['total_hour']=$request->total_hour;
            $dataupdate['updated_at']=date('Y-m-d H:i:s');
            shifts_type::where(['id'=>$id])->update($dataupdate);
            DB::commit();
            return redirect()->route('shifts.index')->with(['success'=>'تم التحديث بنجاح']);
        }catch(\Exception $ex){
            DB::rollBack();
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '. $ex->getMessage()])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        try{
            $data=Shifts_type::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Shifts_type::where(['id'=>$id])->delete();
            return redirect()->route('shifts.index')->with(['success' => 'تم حذف الشيفت بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>' عفوا حدث خطأ ما '.$ex->getMessage()])->withInput();
        }
        
    }
    public function ajaxsearch(Request $request){
        if($request->ajax()){
            $type_search=$request->type_search;
            $hour_from_range=$request->hour_from_range;
            $hour_to_range=$request->hour_to_range;
            if($type_search=='all'){
                $field1="id";
                $op1=">";
                $val1=0;
            }else{
                $field1="type";
                $op1="=";
                $val1=$type_search;
            }
            if($hour_from_range==''){
                $field2="id";
                $op2=">";
                $val2=0;
            }else{
                $field2="tota_hour";
                $op2=">=";
                $val2=$hour_from_range;
            }
            if($hour_to_range==''){
                $field3="id";
                $op3=">";
                $val3=0;
            }else{
                $field3="tota_hour";
                $op3="<=";
                $val3=$hour_to_range;
            }
        $data=Shifts_type::select("*")->where($field1,$op1,$val1)->where($field2,$op2,$val2)->where($field3,$op3,$val3)->orderby("id","DESC")->paginate(paginate_counter);
            return view('admin.shifts.ajax_search',['data'=>$data]);
        }
    }
}
