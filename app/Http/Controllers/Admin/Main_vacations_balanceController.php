<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Finance_cln_period;
use App\Models\Main_vacations_balance;
use App\Models\Department;
use App\Models\Jobs_categories;
use App\Models\Shifts_type;
use App\Models\Branche;
use App\Imports\EmployeeImport;
use App\Exports\EmployeeExport;
use App\Models\Admin_panel_setting;
use App\Models\Finance_calender;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Calculation\Engine\FormattedNumber;

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

    // public function show($id)
    // {
    //     $data = Employee::select('*')->where(['id' => $id])->first();
    //     if (empty($data)) {
    //         return redirect()->back()->with(['error' => 'عفوا حدث خطأ '])->withInput();
    //     } else {
    //         $departments = Department::where('com_code', auth()->guard('admin')->user()->com_code)
    //             ->get(['id', 'dep_name']);
    //         $jobs_categories = Jobs_categories::where('com_code', auth()->guard('admin')->user()->com_code)
    //             ->get(['id', 'job_name']);
    //         $shifts_types = Shifts_type::where('com_code', auth()->guard('admin')->user()->com_code)
    //             ->get(['id', 'type']);
    //         $branches = Branche::where('com_code', auth()->guard('admin')->user()->com_code)
    //             ->get(['id', 'branch_name']);
    //      $this->calculate_vacations_balance($data->employee_id);
    //         return view('admin.Main_vacations_balance.show', ['data' => $data], compact('shifts_types', 'departments', 'jobs_categories', 'branches'));
    //     }

    //     $dataVacations = Main_vacations_balance::select('*')->orderby('id', 'ASC')->paginate(paginate_counter);

    //     return view('admin.Main_vacations_balance.show', ['dataVacations' => $dataVacations]);
    // }
    public function show($id)
    {
        $data = Employee::where('id', $id)->first();

        if (!$data) {
            return redirect()->back()->with(['error' => 'عفوا حدث خطأ'])->withInput();
        }

        // جلب البيانات المساعدة
        $departments = Department::where('com_code', auth()->guard('admin')->user()->com_code)
            ->get(['id', 'dep_name']);

        $jobs_categories = Jobs_categories::where('com_code', auth()->guard('admin')->user()->com_code)
            ->get(['id', 'job_name']);

        $shifts_types = Shifts_type::where('com_code', auth()->guard('admin')->user()->com_code)
            ->get(['id', 'type']);

        $branches = Branche::where('com_code', auth()->guard('admin')->user()->com_code)
            ->get(['id', 'branch_name']);
        $finance_calender_open_year = get_cols_where_row(new Finance_calender(), array('*'), array('com_code' =>auth()->guard('admin')->user()->com_code, "is_open" => 0));
        if (!empty($finance_calender_open_year)) {
            $main_employee_vacation_balance = get_cols_where_row(new Main_vacations_balance(), array('*'), array('com_code' =>auth()->guard('admin')->user()->com_code));
        }
        // حساب الرصيد
        $this->calculate_vacations_balance($data->employee_id);

        // جلب أرصدة الأجازات
        $dataVacations = Main_vacations_balance::orderBy('id', 'ASC')
            ->paginate(paginate_counter);

        // إرسال كل البيانات للعرض
        return view('admin.Main_vacations_balance.show', compact(
            'data',
            'departments',
            'jobs_categories',
            'shifts_types',
            'branches',
            'main_employee_vacation_balance',
            'dataVacations'
        ));
    }

    //دالة احتساب رصيد الاجازات السنوى
    public function calculate_vacations_balance($employee_id)
    {
        $com_code = auth()->user()->com_code;
        $Employee_data = get_cols_where_row(new Employee(), array('*'), array('com_code' => $com_code, 'employee_id' => $employee_id, "functional_status" => 1));
        $admin_panel_settingsData = get_cols_where_row(new Admin_panel_setting(), array("*"), array('com_code' => $com_code));
        if (!empty($Employee_data)) {

            $currentOpenMonth = get_cols_where_row(
                new Finance_cln_period(),
                ['id', 'finance_year', 'year_of_month'],
                ['com_code' => $com_code, 'is_open' => 0]
            );
            // dd($currentOpenMonth);

            // $activeDays = $admin_panel_settingsData['after_days_begain_vacation'];
            // $current_year = $currentOpenMonth['finance_year'];
            // $dateOfActiveFrmoula = date('Y-m-d', strtotime($Employee_data['emp_start_date'] . '+' . $activeDays . 'days'));
            // $datainsert['currentmonth_balance'] = $admin_panel_settingsData['first_balance_begain_vacation'];
            // $datainsert['total_available_balance'] = $admin_panel_settingsData['first_balance_begain_vacation'];
            // $datainsert['net_balance'] = $admin_panel_settingsData['first_balance_begain_vacation'];
            // $datainsert['year_and_month'] = date('Y-m', strtotime($dateOfActiveFrmoula));
            // $datainsert['finance_yr'] = $current_year;
            // $datainsert['employee_id'] = $employee_id;
            // $datainsert['com_code'] = auth()->guard('admin')->user()->com_code;
            // $datainsert['added_by'] = auth()->guard('admin')->user()->id;
            // $datainsert['updated_by'] = auth()->guard('admin')->user()->id;
            // $datainsert['created_at'] = date('Y-m-d H:i:s');
            // $datainsert['updated_at'] = date('Y-m-d H:i:s');
            // insert(new Main_vacations_balance(), $datainsert);
            if (!empty($currentOpenMonth)) {
                if ($Employee_data['vacation_formula'] == 1) {
                    //اول مره ينزله رصيد
                    $now = time();
                    $your_date = strtotime($Employee_data['emp_start_date']);
                    $datediff = $now - $your_date;
                    $diffrence_days = round($datediff / (60 * 60 * 24));
                    
                    if ($diffrence_days >= $admin_panel_settingsData['after_days_begain_vacation']) {
                        $activeDays = number_format($admin_panel_settingsData['after_days_begain_vacation'])*1;
                        $current_year = $currentOpenMonth['finance_year'];
                        $work_year = date('Y', strtotime($Employee_data['emp_start_date']));
                        $dateOfActiveFrmoula = date('Y-m-d', strtotime($Employee_data['emp_start_date'] . '+' . $activeDays . 'days'));

                        if ($current_year == $work_year) {
                            $datainsert['currentmonth_balance'] = $admin_panel_settingsData['first_balance_begain_vacation'];
                            $datainsert['total_available_balance'] = $admin_panel_settingsData['first_balance_begain_vacation'];
                            $datainsert['net_balance'] = $admin_panel_settingsData['first_balance_begain_vacation'];
                        } else {
                            $datainsert['currentmonth_balance'] = $admin_panel_settingsData['monthly_vacation_balance'];
                            $datainsert['total_available_balance'] = $admin_panel_settingsData['monthly_vacation_balance'];
                            $datainsert['net_balance'] = $admin_panel_settingsData['monthly_vacation_balance'];
                        }
                        if ($diffrence_days <= 360) {
                            $datainsert['year_and_month'] = date('Y-m', strtotime($dateOfActiveFrmoula));
                        } else {
                            $datainsert['year_and_month'] = $current_year . '-01';
                        }
                        $datainsert['finance_yr'] = $current_year;
                        $datainsert['employee_id'] = $employee_id;
                        $datainsert['com_code'] = auth()->guard('admin')->user()->com_code;
                        $datainsert['added_by'] = auth()->guard('admin')->user()->id;
                        $datainsert['updated_by'] = auth()->guard('admin')->user()->id;
                        $datainsert['created_at'] = date('Y-m-d H:i:s');
                        $datainsert['updated_at'] = date('Y-m-d H:i:s');
                        $checkExist = get_cols_where_row(new Main_vacations_balance(), array('id'), array('employee_id' => $employee_id, 'finance_yr' => $current_year, 'com_code' => $com_code, 'year_and_month' => $datainsert['year_and_month']));
                        if (empty($checkExist)) {
                            $flag = insert(new Main_vacations_balance(), $datainsert);
                            if ($flag) {
                                $data_to_update['vacation_formula'] = 2;
                                $data_to_update['updated_at'] = date('Y-m-d H:i:s');;
                                $data_to_update['updated_by'] = auth()->guard('admin')->user()->id;
                                update(new Employee(), $data_to_update, array('employee_id' => $employee_id, 'com_code' => $com_code));
                            }
                        }
                    }
                    
                } else {
                    //نزله رصيد
                    $last_added = get_cols_where_row_orderby(new Main_vacations_balance(),array("year_and_month"),array('employee_id' => $employee_id, 'com_code' => $com_code,'finance_yr'=>$currentOpenMonth['finance_year']),'id','DESC');
                    $current_month = intval(date('m',strtotime($currentOpenMonth['finance_year'])));
                    if(!empty($last_added))
                    {
                        if($last_added['year_and_month']!=$currentOpenMonth['finance_year'])
                    {
                        $i=intval(date('m',strtotime($last_added['year_and_month'])));
                        $i+=1;
                        while($i<=$current_month){
                            if ($i<10) {
                                $datainsert['year_and_month'] = $currentOpenMonth['finance_year'] . '-0'.$i;
                            }else{
                                $datainsert['year_and_month'] = $currentOpenMonth['finance_year'] . '-'.$i;
                            }
                        $datainsert['finance_yr'] = $currentOpenMonth['finance_year'];
                        $datainsert['currentmonth_balance'] = $admin_panel_settingsData['monthly_vacation_balance'];
                        $datainsert['total_available_balance'] = $admin_panel_settingsData['monthly_vacation_balance'];
                        $datainsert['net_balance'] = $admin_panel_settingsData['monthly_vacation_balance'];
                        $datainsert['employee_id'] = $employee_id;
                        $datainsert['com_code'] = auth()->guard('admin')->user()->com_code;
                        $datainsert['added_by'] = auth()->guard('admin')->user()->id;
                        $datainsert['updated_by'] = auth()->guard('admin')->user()->id;
                        $datainsert['created_at'] = date('Y-m-d H:i:s');
                        $datainsert['updated_at'] = date('Y-m-d H:i:s');
                        $checkExist = get_cols_where_row(new Main_vacations_balance(), array('id'), array('employee_id' => $employee_id, 'finance_yr'=>$currentOpenMonth['finance_year'], 'com_code' => $com_code, 'year_and_month' => $datainsert['year_and_month']));
                        if (empty($checkExist)) {
                            $flag = insert(new Main_vacations_balance(), $datainsert);

                            $i++;
                        }
                        }
                    }else{

                    }
                }

                    }
            }
        }
    }
}
