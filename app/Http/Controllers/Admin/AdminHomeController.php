<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesPayment;

class AdminHomeController extends Controller
{
    public function index()
    {
        return view('admin.hub');
    }
}
