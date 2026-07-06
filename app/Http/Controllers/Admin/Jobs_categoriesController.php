<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Jobs_categories;
use App\Models\OrgLevel;

class Jobs_categoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Jobs_categories::select('*')->orderby('id', 'DESC')->paginate(paginate_counter);
        return view('admin.jobs_categories.index', ['data' => $data]);
    }

    public function create()
    {
        $comCode = auth()->guard('admin')->user()->com_code;
        $orgLevels = OrgLevel::where('com_code', $comCode)->orderBy('level_order')->get();
        return view('admin.jobs_categories.create', compact('orgLevels'));
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     DB::beginTransaction();
    //     $request->validate([
    //         'job_name'=>'required',
            
          

    //     ],[
    //         'job_name.required'=>'يجب ادخال اسم الوظيفة',
    //     ]);

    //     try{
    //         $datainsert['com_code']=auth()->guard('admin')->user()->com_code;
    //         $datainsert['job_name']=$request->job_name;
    //         $checkIfExist=get_cols_where_row(new Jobs_categories(),array("id"),$datainsert);
    //         if(!empty($checkIfExist)){
    //             return redirect()->back()->with(['error'=>'هذه الوظيفة تم تسجيلها من قبل'])->withInput();
    //         }
    //         $datainsert['added_by'] = auth()->guard('admin')->user()->id;
    //         $datainsert['updated_at']=date('Y-m-d H:i:s');
    //         Jobs_categories::insert($datainsert);
    //         DB::commit();
    //         return redirect()->route('Jobs_categories.index')->with(['success'=>'تم الحفظ بنجاح']);
    //     }catch(\Exception $ex){
    //         DB::rollBack();
    //         return redirect()->back()->with(['error'=>'عفوا حدث خطأ '. $ex->getMessage()])->withInput();
    //     }
    // }

public function store(Request $request)
{
    $request->validate([
        'job_name' => 'required',
    ], [
        'job_name.required' => 'يجب إدخال اسم الوظيفة',
    ]);

    DB::beginTransaction();
    try {
        $admin = auth()->guard('admin')->user();

        $checkexist = Jobs_categories::where([
            'com_code' => $admin->com_code,
            'job_name' => $request->job_name,
        ])->first();

        if (!empty($checkexist)) {
            return redirect()->back()->with(['error' => 'اسم الوظيفة مسجل من قبل'])->withInput();
        }

        Jobs_categories::create([
            'added_by'     => $admin->id,
            'com_code'     => $admin->com_code,
            'job_name'     => $request->job_name,
            'org_level_id' => $request->org_level_id ?: null,
        ]);

        DB::commit();
        return redirect()->route('jobs_categories.index')->with(['success' => 'تم إضافة الوظيفة بنجاح']);
    } catch (\Exception $ex) {
        DB::rollBack();
        return redirect()->back()->with(['error' => 'حدث خطأ: ' . $ex->getMessage()])->withInput();
    }
}


    /**
     * Display the specified resource.
     */
    public function show(Jobs_categories $Jobs_categories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = Jobs_categories::where(['id' => $id])->first();
        if (empty($data)) {
            return redirect()->back()->with(['error' => 'عفوا حدث خطأ'])->withInput();
        }
        $comCode = auth()->guard('admin')->user()->com_code;
        $orgLevels = OrgLevel::where('com_code', $comCode)->orderBy('level_order')->get();
        return view('admin.jobs_categories.update', compact('data', 'orgLevels'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
        DB::beginTransaction();
        $request->validate([
            'job_name'=>'required',
        ],[
            'job_name.required'=>'يجب ادخال اسم الوظيفة',    
        ]);

        try{
            $data=Jobs_categories::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            $admin = auth()->guard('admin')->user();
            Jobs_categories::where(['id' => $id])->update([
                'job_name'     => $request->job_name,
                'org_level_id' => $request->org_level_id ?: null,
                'updated_by'   => $admin->id,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
            DB::commit();
            return redirect()->route('jobs_categories.index')->with(['success'=>'تم التحديث بنجاح']);
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
            $data=Jobs_categories::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Jobs_categories::where(['id'=>$id])->delete();
            return redirect()->route('jobs_categories.index')->with(['success' => 'تم حذف الوظيفة بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>' عفوا حدث خطأ ما '.$ex->getMessage()])->withInput();
        }
        
    }
    public function bulkDelete(Request $request)
    {
        $com_code = auth()->guard('admin')->user()->com_code;
        $search   = trim($request->job_name_search ?? '');

        $query = Jobs_categories::where('com_code', $com_code);
        if ($search !== '') {
            $query->where('job_name', 'LIKE', '%' . $search . '%');
        }

        $ids = $query->pluck('id');

        // Skip jobs that are assigned to employees
        $usedIds = \App\Models\Employee::whereIn('emp_jobs_id', $ids)
            ->pluck('emp_jobs_id')->unique();

        $toDelete  = $ids->diff($usedIds);
        $skipped   = $usedIds->count();
        $deleted   = 0;

        if ($toDelete->isNotEmpty()) {
            $deleted = Jobs_categories::whereIn('id', $toDelete)->delete();
        }

        $message = "تم حذف {$deleted} وظيفة.";
        if ($skipped > 0) {
            $message .= " تم تخطي {$skipped} وظيفة مرتبطة بموظفين.";
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function ajaxsearch(Request $request){
       
        if($request->ajax()){
            $job_name_search=$request->job_name_search;
            if ($job_name_search != "") {
                $field1 = "job_name";
                $op1 = "LIKE"; // Change the operator to LIKE
                $val1 = '%' . $job_name_search . '%'; // Use % for a partial match
                
                $data = Jobs_categories::select("*")->where($field1, $op1, $val1)
                    ->orderBy("id", "DESC")
                    ->paginate(paginate_counter);
            } else {
                // If $job_name_search is empty, get all data without the filter
                $data = Jobs_categories::select("*")
                    ->orderBy("id", "DESC")
                    ->paginate(paginate_counter);
            }
              
                if ($request->ajax()) {
                    // Return partial view for AJAX request
                    return view('admin.jobs_categories.ajax_search', ['data' => $data]);
                } else {
                    // Return full view for initial load or page refresh
                    return view('admin.jobs_categories.index', ['data' => $data]);
                }
        }
        
    }
}