<?php

namespace App\Http\Controllers;

use App\Models\Admin_panel_setting;
use Illuminate\Http\Request;

class AdminPanelSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data= Admin_panel_setting::select('*')->first();

        return view('admin.PanelSetting.index',['data'=>$data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin_panel_setting $admin_panel_setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin_panel_setting $admin_panel_setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin_panel_setting $admin_panel_setting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin_panel_setting $admin_panel_setting)
    {
        //
    }
}
