<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Finance_calender;

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
            "finance_yr" => ["required","unique:Finance_calenders"],
            "start_date" => "required",
            "end_date" => "required",
            // Add validation rules for other fields here if necessary
        ],[
            "finance_yr.required" =>"يجب ادخال السنة المالية",
            "start_date.required" =>"يجب ادخال تاريخ بداية السنة المالية",
            "end_date.required" =>"يجب ادخال تاريخ نهاية السنة المالية"
        ]);
    
        try {
            $createdData = [
                'added_by' => auth()->guard('admin')->user()->id,
                'finance_yr' => $request->finance_yr,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,

            ];
    
            $Finance_calender->create($createdData);
    
            return redirect()->route('finance_calender.index')->with(['success' => 'تم اضافة السنة المالية بنجاح']);
        } catch (\Exception $ex) {
            // Log the exception message for debugging purposes
            Log::error('Error during update: ' . $ex->getMessage());
        
            return redirect()->back()->with(['errorUpdate' => 'حدث خطأ أثناء اضافة السنة المالية: ' . $ex->getMessage()]);
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
    public function destroy(string $id)
    {
        //
    }
}
