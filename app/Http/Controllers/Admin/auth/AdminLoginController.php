<?php

namespace App\Http\Controllers\admin\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class AdminLoginController extends Controller
{
    protected $redirectTo = RouteServiceProvider::AdminHOME;
    // public function __construct()
    // {
    //     $this->middleware('guest:admin')->except('logout');
    // }
    public function login(){
        return view('admin.auth.login');
    }
    public function check(Request $request){
        $request->validate([
            "email"=>["required","string"],
            "password"=>["required","string"]
        ]);
        if(Auth::guard('admin')->attempt(['email'=>$request->email,'password'=>$request->password])){
            return redirect()->route('admin.dashboard.home');
        }else{
            return redirect()->back()->withInput(['email'=>$request->email])->with('errorrLogin','Wrong Credential');
        }
    }
    public function logout(){
        Auth::guard('admin')->logout();
        return redirect()->route('admin.dashboard.login');
    }
}
