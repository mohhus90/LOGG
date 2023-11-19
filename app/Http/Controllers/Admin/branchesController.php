<?php

namespace App\Http\Controllers\Admin;
use App\Models\Branche;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class branchesController extends Controller
{
    public function index()
    {
        $data=get_data_where(new Branche,array("*"));
        // $data= Branche::select('*')->orderby('id','ASC')->paginate(paginate_counter);

        return view('admin.branches.index',['data'=>$data]);
    }

    public function create()
    {
        return view('admin.branches.create');
    }
    public function store(Request $request, Branche $Branche)
    {
        $request->validate([
            "branch_name" => ["required", "unique:branches"],
            
        ],[
            "branch_name.required" => "يجب ادخال اسم الفرع",
        ]);
       
        DB::beginTransaction(); 
        try {
            $createdData = [
                'added_by' => auth()->guard('admin')->user()->id,
                'branch_name' => $request->branch_name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'active' => $request->active,
            ];
            $Branche->create($createdData);
            DB::commit();
            return redirect()->route('branches.index')->with(['success' => 'تم اضافة الفرع بنجاح'])->withInput();
        }catch (\Exception $ex) {
            // Log the exception message for debugging purposes
            DB::rollBack();
            Log::error('Error during update: ' . $ex->getMessage());
            
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ أثناء اضافة الفرع: ' . $ex->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $data=Branche::select('*')->where(['id'=>$id])->first();
        if(empty($data)){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
        }else{
            return view('admin.branches.update',['data'=>$data]);
        }
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(),[
            'branch_name'=>['required',Rule::unique('branches')->ignore($id)],
        ]);
        if($validator->fails()){
            return redirect()->back()->with(['error'=>'قد تم ادخال هذا الفرع من قبل'])->withInput();
        }
        $request->validate([
            "branch_name" => ["required"],
            
        ],[
            "branch_name.required" => "يجب ادخال اسم الفرع",
        ]);
       
        DB::beginTransaction(); 
        try {
            $updatedData = [
                'updated_by' => auth()->guard('admin')->user()->id,
                // 'com_code' => auth()->guard('admin')->user()->com_code,
                'branch_name' => $request->branch_name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'active' => $request->active,
            ];
            Branche::where(['id'=>$id])->update($updatedData);
            DB::commit();
            return redirect()->route('branches.index')->with(['success' => 'تم تحديث الفرع بنجاح'])->withInput();
        }catch (\Exception $ex) {
            // Log the exception message for debugging purposes
            DB::rollBack();
            Log::error('Error during update: ' . $ex->getMessage());
            
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ أثناء تحديث الفرع: ' . $ex->getMessage()])->withInput();
        }
    }

    public function delete(string $id)
    {
        try{
            $data=Branche::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Branche::where(['id'=>$id])->delete();
            return redirect()->route('branches.index')->with(['success' => 'تم حذف الفرع بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '],$ex->getMessage())->withInput();
        }
        
    }



}
