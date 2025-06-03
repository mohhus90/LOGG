<?php

namespace App\Http\Controllers\admin\auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminRegisterController extends Controller
{
    public function register(){
        return view('admin.auth.register');
    }
    public function store(Request $request){
        $adminkey="123";
        if($request->adminkey==$adminkey){
            
            $request->validate([
                
                "name"=>["required","string"],
                "email"=>["required","string"],
                "password"=>["required","string","confirmed"],
                "password_confirmation"=>["required","string"],
                "com_code"=>[]
            ]);
            $data=$request->except('password_confirmation','_token');
            $data['password']=Hash::make($request->password);

            Admin::create($data);
            return redirect()->route('admin.dashboard.home');
        }else{
            return redirect()->back()->with('errorRes','something went wrong');
        }
        

    }
}
