<?php

namespace App\Http\Controllers\Admin;
use App\Models\Department;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DepartmentsController extends Controller
{
    public function index()
    {
        $data=get_data_where(new Department,array("*"));
        // $data= Department::select('*')->orderby('id','ASC')->paginate(paginate_counter);

        return view('admin.departs.index',['data'=>$data]);
    }

    public function create()
    {
        return view('admin.departs.create');
    }
    public function store(Request $request, Department $Department)
    {
        $request->validate([
            "dep_name" => "required",
            
        ],[
            "dep_name.required" => "يجب ادخال اسم الادارة",
        ]);
       
        DB::beginTransaction(); 
        try {
            $com_code=auth()->guard('admin')->user()->com_code;
            $checkexist= Department::select('id')->where(["com_code"=>$com_code,"dep_name"=>$request->dep_name])->first();
            if(!empty($checkexist)){
                return redirect()->back()->with(['error'=>'عفوا اسم الادارة مسجل من قبل'])->withInput();
            }
            $createdData = [
                'added_by' => auth()->guard('admin')->user()->id,
                'com_code' => auth()->guard('admin')->user()->com_code,
                'dep_name' => $request->dep_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'notes' => $request->notes,
            ];
            $Department->create($createdData);
            DB::commit();
            return redirect()->route('departs.index')->with(['success' => 'تم اضافة الادارة بنجاح'])->withInput();
        }catch (\Exception $ex) {
            // Log the exception message for debugging purposes
            DB::rollBack();
            Log::error('Error during update: ' . $ex->getMessage());
            
            return redirect()->back()->with(['errorUpdate' => 'There are Problem: ' . $ex->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $data=Department::select('*')->where(['id'=>$id])->first();
        if(empty($data)){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
        }else{
            return view('admin.departs.update',['data'=>$data]);
        }
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(),[
            'dep_name'=>['required',Rule::unique('departments')->ignore($id)],
        ]);
        if($validator->fails()){
            return redirect()->back()->with(['error'=>'قد تم ادخال هذه الادارة من قبل'])->withInput();
        }
        $request->validate([
            "dep_name" => ["required"],
            
        ],[
            "dep_name.required" => "يجب ادخال اسم الادارة",
        ]);
       
        DB::beginTransaction(); 
        try {
            $updatedData = [
                'updated_by' => auth()->guard('admin')->user()->id,
                'com_code' => auth()->guard('admin')->user()->com_code,
                'dep_name' => $request->dep_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'notes' => $request->notes,
            ];
            Department::where(['id'=>$id])->update($updatedData);
            DB::commit();
            return redirect()->route('departs.index')->with(['success' => 'تم تحديث الادارة بنجاح'])->withInput();
        }catch (\Exception $ex) {
            // Log the exception message for debugging purposes
            DB::rollBack();
            Log::error('Error during update: ' . $ex->getMessage());
            
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ أثناء تحديث الادارة: ' . $ex->getMessage()])->withInput();
        }
    }

    public function delete(string $id)
    {
        try{
            
            $data=Department::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Department::where(['id'=>$id])->delete();
            return redirect()->route('departs.index')->with(['success' => 'تم حذف الادارة بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '],$ex->getMessage())->withInput();
        }
        
    }

}
