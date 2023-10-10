<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\LogController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['verify'=>true]);
// Route::get('/head', function () {
//     return view('layouts.admin');
// })->name('admin.show');
Route::middleware('verified')->group(function(){
    Route::get('/head',[LogController::class,'show_login_view'])->name('admin.show');
});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
