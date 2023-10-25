<?php

namespace App\Http\Controllers\Admin;
use App\Models\Branche;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class branchesController extends Controller
{
    public function index()
    {
        $data= Branche::select('*');

        return view('admin.branches.index',['data'=>$data]);
    }
}
