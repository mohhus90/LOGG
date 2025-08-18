<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
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
       
        // $data= Employee::select('*')->orderby('id','ASC')->paginate(paginate_counter);

        // return view('admin.employees.index',['data'=>$data]);

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
            return view('admin.employees.ajaxsearch', compact('data'))->render();
        }

        return view('admin.Main_vacations_balance.index', compact('data'));

    
    }
}
