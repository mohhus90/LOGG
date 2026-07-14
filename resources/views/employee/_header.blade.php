@php
    $__title = $__title ?? 'بوابة الموظف';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $__title }} — {{ $employee->employee_name_A }}</title>
  <link rel="stylesheet" href="{{ asset('assets/admin/plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap_rtl-v4.2.1/custom_rtl.css') }}">
  <style>
    body { background:#f0f4f8; font-family:'Segoe UI',sans-serif; }
    .emp-header { background:linear-gradient(135deg,#11998e,#38ef7d); color:#fff; padding:20px 0; }
    .req-card { border-radius:12px; border:none; box-shadow:0 2px 15px rgba(0,0,0,.08); }
    .balance-box { background:#fff; border-radius:10px; padding:15px; text-align:center; border-left:4px solid #11998e; }
    .balance-num { font-size:2em; font-weight:700; color:#11998e; }
  </style>
</head>
<body>

<div class="emp-header">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h4 class="mb-0"><i class="fas fa-user-tie ml-2"></i>{{ $__title }}</h4>
        <small>{{ $employee->employee_name_A }} — {{ $employee->employee_id }}</small>
      </div>
      <div>
        <span class="badge badge-light p-2">{{ now()->format('Y-m-d') }}</span>
        <a href="{{ route('employee.logout') }}" class="btn btn-sm btn-outline-light mr-2">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </div>
</div>

@include('employee._nav')

<div class="container mt-4">
  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="fas fa-check-circle ml-1"></i>{{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('error') }}
    </div>
  @endif
