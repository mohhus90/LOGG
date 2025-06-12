<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeesConroller extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Employee::select('*')->orderby('id','DESC')->paginate(paginate_counter);

        return view('admin.employees.index',['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $request->validate([
            'employee_name'=>'required',
            
          

        ],[
            'employee_name.required'=>'يجب ادخال اسم الوظيفة',
        ]);

        try{
            $datainsert = [
                'added_by' => auth()->guard('admin')->user()->id,
                'com_code' => auth()->guard('admin')->user()->com_code,
                'employee_id' => $request->employee_id,
                'fiinger_id' => $request->fiinger_id,
                'employee_name' => $request->employee_name,
                'employee_adress' => $request->employee_adress,
                'emp_gender' => $request->emp_gender,
                'emp_social_status' => $request->emp_social_status,
                'emp_start_date' => $request->emp_start_date,
                'functional_status' => $request->functional_status,
                'resignation_status' => $request->resignation_status,
                'qualification_grade' => $request->qualification_grade,
                'emp_military_status' => $request->emp_military_status,
                'mtivation_type' => $request->mtivation_type,
                'mtivation' => $request->mtivation,
                'sal_cash_visa' => $request->sal_cash_visa,
                'bank_name' => $request->bank_name,
                'bank_account' => $request->bank_account,
                'bank_ID' => $request->bank_ID,
                'bank_branch' => $request->bank_branch,
                

            ];
            $checkIfExist=get_cols_where_row(new Employee(),array("id"),$datainsert);
            if(!empty($checkIfExist)){
                return redirect()->back()->with(['error'=>'هذه الوظيفة تم تسجيلها من قبل'])->withInput();
            }
            $datainsert['updated_at']=date('Y-m-d H:i:s');
            Employee::insert($datainsert);
            DB::commit();
            return redirect()->route('employees.index')->with(['success'=>'تم الحفظ بنجاح']);
        }catch(\Exception $ex){
            DB::rollBack();
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '. $ex->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $Employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data=Employee::select('*')->where(['id'=>$id])->first();
        if(empty($data)){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
        }else{
            return view('admin.employees.update',['data'=>$data]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        DB::beginTransaction();
        $request->validate([
            'employee_name'=>'required',
        ],[
            'employee_name.required'=>'يجب ادخال اسم الوظيفة',    
        ]);

        try{
            $data=Employee::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            $dataupdate['com_code']=auth()->guard('admin')->user()->com_code;
            $dataupdate['employee_name']=$request->employee_name;
            $checkIfExist=get_cols_where_row(new Employee(),array("id"),$dataupdate);
            if(!empty($checkIfExist)){
                return redirect()->back()->with(['error'=>'هذه الوظيفة تم تسجيلها من قبل'])->withInput();
            }
            $dataupdate['updated_by'] = auth()->guard('admin')->user()->id;
            $dataupdate['updated_at']=date('Y-m-d H:i:s');
            Employee::where(['id'=>$id])->update($dataupdate);
            DB::commit();
            return redirect()->route('employees.index')->with(['success'=>'تم التحديث بنجاح']);
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
            $data=Employee::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Employee::where(['id'=>$id])->delete();
            return redirect()->route('jobs_categories.index')->with(['success' => 'تم حذف الوظيفة بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>' عفوا حدث خطأ ما '.$ex->getMessage()])->withInput();
        }
        
    }
    public function ajaxsearch(Request $request){
       
        if($request->ajax()){
            $employee_name_search=$request->employee_name_search;
            if ($employee_name_search != "") {
                $field1 = "employee_name";
                $op1 = "LIKE"; // Change the operator to LIKE
                $val1 = '%' . $employee_name_search . '%'; // Use % for a partial match
                
                $data = Employee::select("*")->where($field1, $op1, $val1)
                    ->orderBy("id", "DESC")
                    ->paginate(paginate_counter);
            } else {
                // If $employee_name_search is empty, get all data without the filter
                $data = Employee::select("*")
                    ->orderBy("id", "DESC")
                    ->paginate(paginate_counter);
            }
              
                if ($request->ajax()) {
                    // Return partial view for AJAX request
                    return view('admin.employees.ajax_search', ['data' => $data]);
                } else {
                    // Return full view for initial load or page refresh
                    return view('admin.employees.index', ['data' => $data]);
                }
        }
        
    }
}
