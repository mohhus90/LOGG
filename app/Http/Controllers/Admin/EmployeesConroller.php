<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Jobs_categories;
use App\Models\Shifts_type;
use App\Models\Branche;
use App\Imports\EmployeeImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EmployeesConroller extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $data = Employee::with([
        'addedBy' => fn($q) => $q->select('id', 'name'),
        'updatedBy' => fn($q) => $q->select('id', 'name')
    ])->paginate(paginate_counter);
    
    return view('admin.employees.index', compact('data'));
       
    }
     public function uploadexcel()
    {
       
        $data = Employee::with([
        'addedBy' => fn($q) => $q->select('id', 'name'),
        'updatedBy' => fn($q) => $q->select('id', 'name')
    ])->paginate(paginate_counter);
    
    return view('admin.employees.uploadexcel', compact('data'));
       
    }
    public function douploadexcel(Request $request)
    {
        $request->validate([
                    'excel_file' => 'required|mimes:xlsx,xls',
                ], [
                    'excel_file.required' => 'اختر ملف اكسيل',
                    'excel_file.mimes' => 'مطلوب اكسيل بامتداد (xlsx , xls) فقط',
                ]);

        Excel::import(new EmployeeImport, $request->excel_file);
    }

    public function create()
   
    {
    // جلب جميع الأقسام من جدول departments بناءً على كود الشركة
    $departments = Department::where('com_code', auth()->guard('admin')->user()->com_code)
                            ->get(['id', 'dep_name']);
    $jobs_categories = Jobs_categories::where('com_code', auth()->guard('admin')->user()->com_code)
                        ->get(['id', 'job_name']);
    $shifts_types = Shifts_type::where('com_code', auth()->guard('admin')->user()->com_code)
                        ->get(['id', 'type']);
    $branches = Branche::where('com_code', auth()->guard('admin')->user()->com_code)
                        ->get(['id', 'branch_name']);
    return view('admin.employees.create', compact('shifts_types','departments','jobs_categories','branches'));
    }
    /**
     * Store a newly created resource in storage.
     */



            public function store(Request $request)
        {
            DB::beginTransaction();

            try {
                // التحقق من صحة البيانات المطلوبة
                $request->validate([
                    'employee_name' => 'required|string',
                    'emp_departments_id' => 'required|exists:departments,id',
                    'shifts_types_id' => 'required|exists:shifts_types,id',
                    'branches_id' => 'required|exists:branches,id',
                    'emp_jobs_id' => 'required|exists:jobs_categories,id',
                    'daily_work_hours' => 'numeric|min:1|max:24',
                    // يمكن إضافة باقي القواعد حسب الحاجة
                ], [
                    'employee_name.required' => 'حقل اسم الموظف مطلوب',
                    'branches_id.required' => 'حقل الفرع مطلوب',
                    'branches_id.exists' => 'الفرع المحدد غير موجود',
                    'shifts_types_id.required' => 'حقل الشيفت مطلوب',
                    'shifts_types_id.exists' => 'الشيفت المحدد غير موجود',
                    'emp_departments_id.required' => 'حقل الادارة مطلوبة',
                    'emp_departments_id.exists' => 'الادارة المحدد غير موجودة',
                    'emp_jobs_id.required' => 'حقل الوظيفة مطلوبة',
                    'emp_jobs_id.exists' => 'الوظيفة المحددة غير موجودة',
                    'daily_work_hours.min' => 'يجب أن لا يقل عدد الساعات عن 1',
                    'daily_work_hours.max' => 'يجب أن لا يزيد عدد الساعات عن 24',
                ]);

                // تجهيز البيانات للحفظ
                $employeeData = [
                    'added_by' => auth()->guard('admin')->user()->id,
                    'com_code' => auth()->guard('admin')->user()->com_code,
                    'employee_id' => $request->employee_id,
                    'finger_id' => $request->finger_id,
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
                    'daily_work_hours' => $request->daily_work_hours,
                    'emp_departments_id' => $request->emp_departments_id,
                    'emp_jobs_id' => $request->emp_jobs_id,
                    'shifts_types_id' => $request->shifts_types_id,
                    'branches_id' => $request->branches_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // التحقق من عدم تكرار البيانات
                $existingEmployee = Employee::where($employeeData)->first();
                if ($existingEmployee) {
                    return redirect()->back()
                        ->with('error', 'هذا الموظف مسجل مسبقاً')
                        ->withInput();
                }

                // حفظ البيانات
                Employee::create($employeeData);

                DB::commit();
                return redirect()->route('employees.index')
                    ->with('success', 'تم إضافة الموظف بنجاح')->withInput();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during save: ' . $e->getMessage());
                return redirect()->back()
                    ->with('error', 'حدث خطأ: ' . $e->getMessage())
                    ->withInput();
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
