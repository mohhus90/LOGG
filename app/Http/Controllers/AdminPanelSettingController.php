<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class AdminPanelSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Admin_panel_setting::select('*')->first();

        return view('admin.PanelSetting.index',['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin_panel_setting $admin_panel_setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin_panel_setting $admin_panel_setting)
    {
        $data= Admin_panel_setting::select('*')->first();

        return view('admin.PanelSetting.edit',['data'=>$data]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin_panel_setting $admin_panel_setting)
{
    // $request->validate([
    //     "com_name" => ["string"],
    //     "email"=>['required',"string"]
    //     // Add validation rules for other fields here if necessary
    // ],[
    //     "email.required" =>"يجب ادخال الايميل"
    // ]);

    try {
        $updatedData = [
            'updated_by' => auth()->guard('admin')->user()->id,
            'com_name' => $request->com_name,
            'com_code' => $request->com_code,
            'saysem_status' => $request->saysem_status,
            'phone' => $request->phone,
            'email' => $request->email,
            'after_minute_calc_delay' => $request->after_minute_calc_delay,
            'after_minute_calc_early' => $request->after_minute_calc_early,
            'after_minute_quarterday' => $request->after_minute_quarterday,
            'after_time_half_daycut' => $request->after_time_half_daycut,
            'after_time_allday_daycut' => $request->after_time_allday_daycut,
            'monthly_vacation_balance' => $request->monthly_vacation_balance,
            'first_balance_begain_vacation' => $request->first_balance_begain_vacation,
            'after_days_begain_vacation' => $request->after_days_begain_vacation,
            'sanctions_value_first_abcence' => $request->sanctions_value_first_abcence,
            'sanctions_value_second_abcence' => $request->sanctions_value_second_abcence,
            'sanctions_value_third_abcence' => $request->sanctions_value_third_abcence,
            'sanctions_value_forth_abcence' => $request->sanctions_value_forth_abcence,
        ];

        $admin_panel_setting->where('id', $request->id)->update($updatedData);

        return redirect()->route('generalsetting.index')->with(['success' => 'تم تحديث البيانات بنجاح']);
    } catch (\Exception $ex) {
        // Log the exception message for debugging purposes
        Log::error('Error during update: ' . $ex->getMessage());
    
        return redirect()->back()->with(['errorUpdate' => 'حدث خطأ أثناء التحديث: ' . $ex->getMessage()]);
    }
}
////////////////////////////////
//     public function update(Request $request, Admin_panel_setting $admin_panel_setting)
// {
//     $request->validate([
//         "com_name" => ["string"],
//         // Add validation rules for other fields here if necessary
//     ]);

//     try {
//         $updatedData = [
//             'updated_by' => auth()->guard('admin')->user()->id,
//             'com_name' => $request->com_name,
//             'saysem_status' => $request->saysem_status,
//             'phone' => $request->phone,
//             'email' => $request->email,
//             'after_minute_calc_delay' => $request->after_minute_calc_delay,
//             'after_minute_calc_early' => $request->after_minute_calc_early,
//             'after_minute_quarterday' => $request->after_minute_quarterday,
//             'after_time_half_daycut' => $request->after_time_half_daycut,
//             'after_time_allday_daycut' => $request->after_time_allday_daycut,
//             'monthly_vacation_balance' => $request->monthly_vacation_balance,
//             'first_balance_begain_vacation' => $request->first_balance_begain_vacation,
//             'after_days_begain_vacation' => $request->after_days_begain_vacation,
//             'sanctions_value_first_abcence' => $request->sanctions_value_first_abcence,
//             'sanctions_value_second_abcence' => $request->sanctions_value_second_abcence,
//             'sanctions_value_third_abcence' => $request->sanctions_value_third_abcence,
//             'sanctions_value_forth_abcence' => $request->sanctions_value_forth_abcence,
//         ];

//         $admin_panel_setting->where('id', $request->id)->update($updatedData);

//         return redirect()->route('generalsetting.index')->with(['success' => 'تم تحديث البيانات بنجاح']);
//     } catch (\Exception $ex) {
//         return redirect()->back()->with(['errorUpdate' => 'عفوا قد حدث خطأ']);
//     }
// }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin_panel_setting $admin_panel_setting)
    {
        //
    }
}
