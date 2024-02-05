<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jobs_categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Jobs_categoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Jobs_categorie::select('*')->orderby('id','DESC')->paginate(paginate_counter);

        return view('admin.jobs_categories.index',['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.jobs_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $request->validate([
            'job_name'=>'required',
            
          

        ],[
            'job_name.required'=>'يجب ادخال اسم الوظيفة',
        ]);

        try{
            $datainsert['com_code']=auth()->guard('admin')->user()->com_code;
            $datainsert['job_name']=$request->job_name;
            $checkIfExist=get_cols_where_row(new Jobs_categorie(),array("id"),$datainsert);
            if(!empty($checkIfExist)){
                return redirect()->back()->with(['error'=>'هذه الوظيفة تم تسجيلها من قبل'])->withInput();
            }
            $datainsert['added_by'] = auth()->guard('admin')->user()->id;
            $datainsert['updated_at']=date('Y-m-d H:i:s');
            Jobs_categorie::insert($datainsert);
            DB::commit();
            return redirect()->route('jobs_categories.index')->with(['success'=>'تم الحفظ بنجاح']);
        }catch(\Exception $ex){
            DB::rollBack();
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '. $ex->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Jobs_categorie $Jobs_categorie)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data=Jobs_categorie::select('*')->where(['id'=>$id])->first();
        if(empty($data)){
            return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
        }else{
            return view('admin.jobs_categories.update',['data'=>$data]);
        }
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
            $data=Jobs_categorie::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            $dataupdate['com_code']=auth()->guard('admin')->user()->com_code;
            $dataupdate['job_name']=$request->job_name;
            $checkIfExist=get_cols_where_row(new Jobs_categorie(),array("id"),$dataupdate);
            if(!empty($checkIfExist)){
                return redirect()->back()->with(['error'=>'هذه الوظيفة تم تسجيلها من قبل'])->withInput();
            }
            $dataupdate['updated_by'] = auth()->guard('admin')->user()->id;
            $dataupdate['updated_at']=date('Y-m-d H:i:s');
            Jobs_categorie::where(['id'=>$id])->update($dataupdate);
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
            $data=Jobs_categorie::select('*')->where(['id'=>$id])->first();
            if(empty($data)){
                return redirect()->back()->with(['error'=>'عفوا حدث خطأ '])->withInput(); 
            }
            Jobs_categorie::where(['id'=>$id])->delete();
            return redirect()->route('jobs_categories.index')->with(['success' => 'تم حذف الوظيفة بنجاح'])->withInput();

        }catch(\Exception $ex){
            return redirect()->back()->with(['error'=>' عفوا حدث خطأ ما '.$ex->getMessage()])->withInput();
        }
        
    }
    public function ajaxsearch(Request $request){
       
        if($request->ajax()){
            $job_name_search=$request->job_name_search;
            if ($job_name_search != "") {
                $field1 = "job_name";
                $op1 = "LIKE"; // Change the operator to LIKE
                $val1 = '%' . $job_name_search . '%'; // Use % for a partial match
                
                $data = Jobs_categorie::select("*")->where($field1, $op1, $val1)
                    ->orderBy("id", "DESC")
                    ->paginate(paginate_counter);
            } else {
                // If $job_name_search is empty, get all data without the filter
                $data = Jobs_categorie::select("*")
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