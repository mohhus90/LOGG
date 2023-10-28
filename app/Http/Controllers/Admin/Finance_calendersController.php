<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance_calender;
use App\Models\Month;
use App\Models\Finance_cln_period;
use DateInterval;
use DatePeriod;
use DateTime;

class Finance_calendersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Finance_calender::select('*')->orderby('finance_yr','DESC')->paginate(paginate_counter);

        return view('admin.finance_calender.index',['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.finance_calender.create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request, Finance_calender $Finance_calender)
    {
        $request->validate([
            "finance_yr" => ["required", "unique:finance_calenders"],
            "start_date" => "required",
            "end_date" => "required",
            // "is_open" => "required", 
            // Add validation rule for is_open field
            // Add validation rules for other fields here if necessary
        ],[
            "finance_yr.required" => "يجب ادخال السنة المالية",
            "start_date.required" => "يجب ادخال تاريخ بداية السنة المالية",
            "end_date.required" => "يجب ادخال تاريخ نهاية السنة المالية",
            // "is_open.required" => "يجب ادخال حقل الإغلاق",
        ]);
        DB::beginTransaction(); 
        try {
            $createdData = [
                'added_by' => auth()->guard('admin')->user()->id,
                'finance_yr' => $request->finance_yr,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,

            ];
    
            $flag=$Finance_calender->insert($createdData);
            if($flag){
                $dataParent=$Finance_calender->select('id')->where($createdData)->first();
                $startDate=new DateTime($request->start_date);
                $endDate=new DateTime($request->end_date);
                $dateInterval=new DateInterval('P1M');
                $datePeriod=new DatePeriod($startDate,$dateInterval,$endDate);

                foreach($datePeriod as $date){
                    $datamonth['finance_calenders_id']=$dataParent['id'];
                    $monthname_en=$date->format('F');
                    $dataParentMontn=Month::select('id')->where(['monthe_name_en'=>$monthname_en])->first();
                    $datamonth['month_id']=$dataParentMontn['id'];
                    $datamonth['finance_year']=$createdData['finance_yr'];
                    $datamonth['start_date']=date('Y-m-01',strtotime($date->format('Y-m-d')));
                    $datamonth['end_date']=date('Y-m-t',strtotime($date->format('Y-m-d')));
                    $datamonth['year_of_month']=date('Y-m',strtotime($date->format('Y-m-d')));
                    $dateDiff=strtotime($datamonth['end_date'])-strtotime($datamonth['start_date']);
                    $datamonth['number_of_days']=round($dateDiff/(60*60*24))+1;
                    $datamonth['added_by'] = auth()->guard('admin')->user()->id;
                    $datamonth['updated_by'] = auth()->guard('admin')->user()->id;
                    $datamonth['created_at']=date('Y-m-d H:i:s');
                    $datamonth['updated_at']=date('Y-m-d H:i:s');
                    $datamonth['start_date_finger_print']=date('Y-m-01',strtotime($date->format('Y-m-d')));
                    $datamonth['end_date_finger_print']=date('Y-m-01',strtotime($date->format('Y-m-d')));
                    Finance_cln_period::insert($datamonth);
                }
            }
            DB::commit();
            return redirect()->route('finance_calender.index')->with(['success' => 'تم اضافة السنة المالية بنجاح'])->withInput();
        } catch (\Exception $ex) {
            // Log the exception message for debugging purposes
            DB::rollBack();
            Log::error('Error during update: ' . $ex->getMessage());
            
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ أثناء اضافة السنة المالية: ' . $ex->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(string $id)
    {
        try{
            $data=Finance_calender::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Finance_calender::where(['id'=>$id])->delete();
            return redirect()->route('finance_calender.index')->with(['success' => 'تم حذف السنة المالية بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '],$ex->getMessage())->withInput();
        }
        
    }
}
