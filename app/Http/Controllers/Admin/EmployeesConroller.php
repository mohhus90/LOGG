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
use App\Exports\EmployeeExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EmployeesConroller extends Controller

{
      public function export() 
    {
        return Excel::download(new EmployeeExport, 'Employee.xlsx');
        
    }
    

    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $data=get_data_where(new Employee,array("*"));
        $data= Employee::select('*')->orderby('id','ASC')->paginate(paginate_counter);

        return view('admin.employees.index',['data'=>$data]);
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
        return redirect()->route('employees.index')
                    ->with('success', 'تم الإضافة بنجاح')->withInput();
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
    // التحقق من صحة البيانات قبل الدخول في الـ try/catch
    $request->validate([
        'employee_name_A' => 'required|string',
        'employee_name_E' => 'nullable|string',
        'employee_id' => 'required|unique:employees,employee_id',
        'national_id' => 'required|unique:employees,national_id',
        'bank_account' => 'nullable|unique:employees,bank_account',
        'emp_departments_id' => 'required|exists:departments,id',
        'shifts_types_id' => 'required|exists:shifts_types,id',
        'branches_id' => 'required|exists:branches,id',
        'emp_jobs_id' => 'required|exists:jobs_categories,id',
        'daily_work_hours' => 'numeric|min:1|max:24',
        'finger_id' => 'nullable|string',
        'employee_address' => 'nullable|string',
        'emp_gender' => 'nullable|string',
        'emp_social_status' => 'nullable|string',
        'emp_start_date' => 'nullable|date',
        'insurance_status' => 'nullable|string',
        'resignation_status' => 'nullable|string',
        'qualification_grade' => 'nullable|string',
        'emp_military_status' => 'nullable|string',
        'motivation' => 'nullable|numeric',
        'sal_cash_visa' => 'nullable|string',
        'bank_name' => 'nullable|string',
        'bank_ID' => 'nullable|string',
        'bank_branch' => 'nullable|string',
        'emp_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
    ], [
        'employee_name_A.required' => 'حقل اسم الموظف مطلوب',
        'employee_id.required' => 'حقل كود الموظف مطلوب',
        'employee_id.unique' => 'كود الموظف تم إدخاله مسبقًا',
        'national_id.required' => 'حقل الرقم القومي مطلوب',
        'national_id.unique' => 'هذا الرقم القومي تم إدخاله مسبقًا',
        'bank_account.unique' => 'هذا الحساب البنكي تم إدخاله مسبقًا',
        'branches_id.required' => 'حقل الفرع مطلوب',
        'branches_id.exists' => 'الفرع المحدد غير موجود',
        'shifts_types_id.required' => 'حقل الشيفت مطلوب',
        'shifts_types_id.exists' => 'الشيفت المحدد غير موجود',
        'emp_departments_id.required' => 'حقل الإدارة مطلوب',
        'emp_departments_id.exists' => 'الإدارة المحددة غير موجودة',
        'emp_jobs_id.required' => 'حقل الوظيفة مطلوب',
        'emp_jobs_id.exists' => 'الوظيفة المحددة غير موجودة',
        'daily_work_hours.min' => 'يجب ألا يقل عدد الساعات عن 1',
        'daily_work_hours.max' => 'يجب ألا يزيد عدد الساعات عن 24',
    ]);

    DB::beginTransaction();

    try {
        $imageName = null;

        // ✅ معالجة الصورة
        if ($request->hasFile('emp_photo')) {
            $image = $request->file('emp_photo');
            $imageName = time() . '_' . $image->getClientOriginalName();
            // $destinationPath = public_path('assets/admin/uploads');
            $image->move('assets/admin/uploads', $imageName);
        }

        // ✅ تجهيز البيانات للحفظ
        $employeeData = [
            'added_by' => auth()->guard('admin')->user()->id,
            'com_code' => auth()->guard('admin')->user()->com_code,
            'employee_id' => $request->employee_id,
            'finger_id' => $request->finger_id,
            'employee_name_A' => $request->employee_name_A,
            'employee_name_E' => $request->employee_name_E,
            'employee_address' => $request->employee_address,
            'emp_gender' => $request->emp_gender,
            'emp_social_status' => $request->emp_social_status,
            'emp_start_date' => $request->emp_start_date,
            'insurance_status' => $request->insurance_status,
            'resignation_status' => $request->resignation_status,
            'qualification_grade' => $request->qualification_grade,
            'emp_qualification' => $request->emp_qualification,
            'qualification_year' => $request->qualification_year,
            'resignation_date' => $request->resignation_date,
            'resignation_cause' => $request->resignation_cause,
            'emp_home_tel' => $request->emp_home_tel,
            'emp_mobile' => $request->emp_mobile,
            'emp_email' => $request->emp_email,
            'emp_photo' => $imageName, // حفظ الصورة
            'emp_cv' => $request->emp_cv,
            'birth_date' => $request->birth_date,
            'emp_sal' => $request->emp_sal,
            'emp_sal_insurance' => $request->emp_sal_insurance,
            'medical_insurance' => $request->medical_insurance,
            'emp_fixed_allowances' => $request->emp_fixed_allowances,
            'emp_military_status' => $request->emp_military_status,
            'motivation' => $request->motivation,
            'national_id' => $request->national_id,
            'insurance_no' => $request->insurance_no,
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

        // ✅ حفظ البيانات
        Employee::create($employeeData);

        DB::commit();
        return redirect()->route('employees.index')
            ->with('success', 'تم إضافة الموظف بنجاح');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error during employee save: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'حدث خطأ غير متوقع: ' . $e->getMessage())
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
            $departments = Department::where('com_code', auth()->guard('admin')->user()->com_code)
                                    ->get(['id', 'dep_name']);
            $jobs_categories = Jobs_categories::where('com_code', auth()->guard('admin')->user()->com_code)
                                ->get(['id', 'job_name']);
            $shifts_types = Shifts_type::where('com_code', auth()->guard('admin')->user()->com_code)
                                ->get(['id', 'type']);
            $branches = Branche::where('com_code', auth()->guard('admin')->user()->com_code)
                        ->get(['id', 'branch_name']);
            return view('admin.employees.update',['data'=>$data],compact('shifts_types','departments','jobs_categories','branches'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    // التحقق من الحقول المكررة
    $validator1 = Validator::make($request->all(), [
        'employee_id' => ['required', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator1->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال كود الموظف هذا لموظف آخر'])->withInput();
    }

    $validator2 = Validator::make($request->all(), [
        'national_id' => ['required', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator2->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال الرقم القومي هذا لموظف آخر'])->withInput();
    }

    $validator3 = Validator::make($request->all(), [
        'insurance_no' => ['required', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator3->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال الرقم التأميني هذا لموظف آخر'])->withInput();
    }

    $validator4 = Validator::make($request->all(), [
        'bank_account' => ['nullable', Rule::unique('employees')->ignore($id)],
    ]);
    if ($validator4->fails()) {
        return redirect()->back()->with(['error' => 'قد تم إدخال حساب البنك هذا لموظف آخر'])->withInput();
    }

    // التحقق من الحقول الأخرى
    $request->validate([
        'employee_name_A' => 'required|string',
        'employee_name_E' => 'required|string',
        'employee_id' => 'required',
        'national_id' => 'required',
        'emp_departments_id' => 'required|exists:departments,id',
        'shifts_types_id' => 'required|exists:shifts_types,id',
        'branches_id' => 'required|exists:branches,id',
        'emp_jobs_id' => 'required|exists:jobs_categories,id',
        'daily_work_hours' => 'numeric|min:1|max:24',
        'finger_id' => 'nullable|string',
        'employee_address' => 'nullable|string',
        'emp_gender' => 'nullable|string',
        'emp_social_status' => 'nullable|string',
        'emp_start_date' => 'nullable|date',
        'insurance_status' => 'nullable|string',
        'resignation_status' => 'nullable|string',
        'qualification_grade' => 'nullable|string',
        'emp_military_status' => 'nullable|string',
        'motivation' => 'nullable|numeric',
        'sal_cash_visa' => 'nullable|string',
        'bank_name' => 'nullable|string',
        'bank_ID' => 'nullable|string',
        'bank_branch' => 'nullable|string',
        'emp_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5048',
    ], [
        'employee_name_A.required' => 'حقل اسم الموظف مطلوب',
        'employee_name_E.required' => 'حقل اسم الموظف مطلوب',
        'employee_id.required' => 'حقل كود الموظف مطلوب',
        'national_id.required' => 'حقل الرقم القومي مطلوب',
        'branches_id.required' => 'حقل الفرع مطلوب',
        'branches_id.exists' => 'الفرع المحدد غير موجود',
        'shifts_types_id.required' => 'حقل الشيفت مطلوب',
        'shifts_types_id.exists' => 'الشيفت المحدد غير موجود',
        'emp_departments_id.required' => 'حقل الإدارة مطلوب',
        'emp_departments_id.exists' => 'الإدارة المحددة غير موجودة',
        'emp_jobs_id.required' => 'حقل الوظيفة مطلوب',
        'emp_jobs_id.exists' => 'الوظيفة المحددة غير موجودة',
        'daily_work_hours.min' => 'يجب ألا يقل عدد الساعات عن 1',
        'daily_work_hours.max' => 'يجب ألا يزيد عدد الساعات عن 24',
    ]);

    DB::beginTransaction();

    try {
        $employee = Employee::findOrFail($id);

        // الاحتفاظ بالاسم القديم
        $imageName = $employee->emp_photo;

        // معالجة رفع صورة جديدة
        if ($request->hasFile('emp_photo')) {
            $image = $request->file('emp_photo');
            $imageName = time() . '_' . $image->getClientOriginalName();
            // $destinationPath = public_path('assets/admin/uploads');
            $image->move('assets/admin/uploads', $imageName);

            // حذف الصورة القديمة إن وجدت
            if ($employee->emp_photo && file_exists('assets/admin/uploads' . '/' . $employee->emp_photo)) {
                unlink('assets/admin/uploads'. '/' . $employee->emp_photo);
            }
        }

        // بيانات التحديث
        $dataupdate = [
            'updated_by' => auth()->guard('admin')->user()->id,
            'com_code' => auth()->guard('admin')->user()->com_code,
            'employee_id' => $request->employee_id,
            'finger_id' => $request->finger_id,
            'employee_name_A' => $request->employee_name_A,
            'employee_name_E' => $request->employee_name_E,
            'employee_address' => $request->employee_address,
            'emp_gender' => $request->emp_gender,
            'emp_social_status' => $request->emp_social_status,
            'emp_start_date' => $request->emp_start_date,
            'insurance_status' => $request->insurance_status,
            'resignation_status' => $request->resignation_status,
            'qualification_grade' => $request->qualification_grade,
            'emp_qualification' => $request->emp_qualification,
            'qualification_year' => $request->qualification_year,
            'resignation_date' => $request->resignation_date,
            'resignation_cause' => $request->resignation_cause,
            'emp_home_tel' => $request->emp_home_tel,
            'emp_mobile' => $request->emp_mobile,
            'emp_email' => $request->emp_email,
            'emp_photo' => $imageName, // ✅ نستخدم اسم الصورة الصحيح
            'emp_cv' => $request->emp_cv,
            'birth_date' => $request->birth_date,
            'emp_sal' => $request->emp_sal,
            'emp_sal_insurance' => $request->emp_sal_insurance,
            'medical_insurance' => $request->medical_insurance,
            'emp_fixed_allowances' => $request->emp_fixed_allowances,
            'emp_military_status' => $request->emp_military_status,
            'motivation' => $request->motivation,
            'national_id' => $request->national_id,
            'insurance_no' => $request->insurance_no,
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
            'updated_at' => now(),
        ];

        // تحديث البيانات
        $employee->update($dataupdate);

        DB::commit();
        return redirect()->route('employees.index')->with(['success' => 'تم التحديث بنجاح']);

    } catch (\Exception $ex) {
        DB::rollBack();
        return redirect()->back()->with(['error' => 'عفواً حدث خطأ: ' . $ex->getMessage()])->withInput();
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
            return redirect()->route('employees.index')->with(['success' => 'تم حذف الوظيفة بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>' عفوا حدث خطأ ما '.$ex->getMessage()])->withInput();
        }
        
    }
    public function ajaxsearch(Request $request){
       
        if($request->ajax()){
            $employee_name_A_search=$request->employee_name_A_search;
            if ($employee_name_A_search != "") {
                $field1 = "employee_name_A";
                $op1 = "LIKE"; // Change the operator to LIKE
                $val1 = '%' . $employee_name_A_search . '%'; // Use % for a partial match
                
                $data = Employee::select("*")->where($field1, $op1, $val1)
                    ->orderBy("id", "DESC")
                    ->paginate(paginate_counter);
            } else {
                // If $employee_name_A_search is empty, get all data without the filter
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
