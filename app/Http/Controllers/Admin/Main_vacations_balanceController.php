<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\finance_cln_period;
use App\Models\Main_vacations_balance;
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

class Main_vacations_balanceController extends Controller
{
     public function index(Request $request)

    {
       
        $employee_name_A_search = $request->employee_name_A_search;
        $employee_id_search = $request->employee_id_search;

        if ($employee_name_A_search == 'all' || empty($employee_name_A_search)) {
            $field1 = "id";
            $op1 = ">";
            $val1 = 0;
        } else {
            $field1 = "employee_name_A";
            $op1 = "like";
            $val1 = '%' . $employee_name_A_search . '%';
        }
        if ($employee_id_search == 'all' || empty($employee_id_search)) {
            $field2 = "id";
            $op2 = ">";
            $val2 = 0;
        } else {
            $field2 = "employee_id";
            $op2 = "like";
            $val2 = '%' . $employee_id_search . '%';
        }

        $data = Employee::where($field1, $op1, $val1)
            ->where($field2, $op2, $val2)
            ->orderBy("id", "DESC")
            ->paginate(10);

        if ($request->ajax()) {
            return view('admin.Main_vacations_balance.ajaxsearch', compact('data'))->render();
        }

        return view('admin.Main_vacations_balance.index', compact('data'));

        
    
    }

    public function show($id)
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
            return view('admin.Main_vacations_balance.show',['data'=>$data],compact('shifts_types','departments','jobs_categories','branches'));
        }
         $dataVacations= Main_vacations_balance::select('*')->orderby('id','ASC')->paginate(paginate_counter);

        return view('admin.Main_vacations_balance.show',['dataVacations'=>$dataVacations]);

    }

    //دالة احتساب رصيد الاجازات السنوى
    public function calculate_vacations_balance($employee_id)
    {
        $com_code = auth()->user()->com_code;
        $Employee_data=get_cols_where_row(new Employee(),array('*'),array('com_code'=>$com_code,'employee_id'=>$employee_id,"functional_status"=>1));
        if (!empty($Employee_data)) {
            $currentOpenMonth = get_cols_where_row(new finance_cln_period(),array('*'),array('com_code'=>$com_code,'employee_id'=>$employee_id,"functional_status"=>1));
            if ($Employee_data['vacation_formula']==0) {
                //اول مره ينزله رصيد
            }else{
                 //نزله رصيد
            }
        }

    }

}
