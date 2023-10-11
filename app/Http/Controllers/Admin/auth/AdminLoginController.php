<?php

namespace App\Http\Controllers\admin\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function login(){
        return view('admin.auth.login');
    }
    public function check(){
        //logic
    }
}
