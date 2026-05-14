<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use App\Http\Requests\LoginRequest;
class LoginController extends Controller
{
    public function show_login_view(){
        return view('layouts.admin');
        // $asmin['name']='moh hus'; 
        // $asmin['email']='moh@ccc';  
        // $asmin['username']='moh';  
        // $asmin['password']='123';  
        // $asmin['added_by']=1;  
        // $asmin['updated_by']=1;  
        // $asmin['active']=1;  
        // $asmin['date']=Date("Y-m-d");  
        // $asmin['com_code']=1;
        // Admin::create($asmin);
    }
    // public function login(LoginRequest $request){
    //     // if(auth()->guard('admin')->attempt(["username"=>$request->input("username"),"password"=>$request->input("password")])){
    //     //     return "done";
            
    //     // }
    //     if (auth()->guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
    //                     // Authentication was successful
    //                     return "done";
    //                     // return redirect()->route('admin.dashboard'); // You can customize this redirection
    //                 }
    //     else{
    //         return redirect()->route('admin.showlogin')->with(['erorr'=>'المستخدم غير موجود']);
            
    //     }
    // }
}
// namespace App\Http\Controllers\Admin;

// use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
// use Auth;

// class LoginController extends Controller
// {
//     // Show the login form
//     public function showLoginForm()
//     {
//         return "view('admin.auth.login')";
//     }

//     // Handle login form submission
//     public function login(Request $request)
//     {
//         // Validate the form data
//         $request->validate([
//             'username' => 'required',
//             'password' => 'required',
//         ]);

//         // Attempt to log in the user
//         if (auth()->guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
//             // Authentication was successful
//             return redirect()->route('admin.dashboard'); // You can customize this redirection
//         }

//         // Authentication failed
//         return redirect()->route('admin.login')->with('error', 'Invalid login credentials');
//     }

//     // Logout
//     public function logout()
//     {
//         auth()->guard('admin')->logout();
//         return redirect()->route('admin.login');
//     }
// }
