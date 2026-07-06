@extends('admin.layouts.admin')
@section('title') لوحة التحكم الرئيسية @endsection
@section('start') الرئيسية @endsection
@section('home') <a href="{{ route('admin.dashboard.home.page') }}">الرئيسية</a> @endsection
@section('startpage') نظرة عامة @endsection

@section('content')
<div class="col-12">

    {{-- ══ HR Stats ══ --}}
    <h5 class="mb-3 text-muted border-bottom pb-2">
        <i class="fas fa-users ml-2 text-indigo"></i> الموارد البشرية
    </h5>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $hrStats['employees'] ?? 0 }}</h3>
                    <p>الموظفون النشطون</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('employees.index') }}" class="small-box-footer">
                    عرض الكل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3><i class="fas fa-fingerprint" style="font-size:2rem"></i></h3>
                    <p>الحضور والانصراف</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <a href="{{ route('attendance.index') }}" class="small-box-footer">
                    عرض الكل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3><i class="fas fa-money-check-alt" style="font-size:2rem"></i></h3>
                    <p>كشف الرواتب</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <a href="{{ route('payroll.index') }}" class="small-box-footer">
                    عرض الكل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3><i class="fas fa-chart-line" style="font-size:2rem"></i></h3>
                    <p>مؤشرات الأداء</p>
                </div>
                <div class="icon"><i class="fas fa-tachometer-alt"></i></div>
                <a href="{{ route('kpi.definitions') }}" class="small-box-footer">
                    عرض الكل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ══ Sales Stats ══ --}}
    <h5 class="mb-3 mt-2 text-muted border-bottom pb-2">
        <i class="fas fa-chart-line ml-2 text-success"></i> المبيعات — هذا الشهر
    </h5>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($salesStats['invoices_month'] ?? 0, 2) }}</h3>
                    <p>إجمالي الفواتير</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
                <a href="{{ route('sales_invoices.index') }}" class="small-box-footer">
                    عرض الفواتير <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ number_format($salesStats['collected_month'] ?? 0, 2) }}</h3>
                    <p>المحصّل هذا الشهر</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <a href="{{ route('sales_payments.index') }}" class="small-box-footer">
                    عرض المدفوعات <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $salesStats['pending_orders'] ?? 0 }}</h3>
                    <p>أوامر بيع جارية</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <a href="{{ route('sales_orders.index') }}" class="small-box-footer">
                    عرض الأوامر <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-orange">
                <div class="inner">
                    <h3>{{ number_format($salesStats['total_debt'] ?? 0, 2) }}</h3>
                    <p>إجمالي الديون المستحقة</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
                <a href="{{ route('sales_reports.debt') }}" class="small-box-footer">
                    عرض الديون <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- ══ Quick Actions ══ --}}
    <h5 class="mb-3 mt-2 text-muted border-bottom pb-2">
        <i class="fas fa-bolt ml-2 text-warning"></i> إجراءات سريعة
    </h5>
    <div class="row">
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <a href="{{ route('sales_invoices.create') }}" class="btn btn-outline-success btn-block py-3">
                <i class="fas fa-file-invoice fa-lg mb-1 d-block"></i>
                فاتورة جديدة
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <a href="{{ route('sales_orders.create') }}" class="btn btn-outline-primary btn-block py-3">
                <i class="fas fa-shopping-cart fa-lg mb-1 d-block"></i>
                أمر بيع جديد
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <a href="{{ route('sales_customers.create') }}" class="btn btn-outline-info btn-block py-3">
                <i class="fas fa-user-plus fa-lg mb-1 d-block"></i>
                عميل جديد
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <a href="{{ route('sales_payments.create') }}" class="btn btn-outline-warning btn-block py-3">
                <i class="fas fa-money-bill-wave fa-lg mb-1 d-block"></i>
                تسجيل دفعة
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <a href="{{ route('employees.create') }}" class="btn btn-outline-secondary btn-block py-3">
                <i class="fas fa-user-plus fa-lg mb-1 d-block"></i>
                موظف جديد
            </a>
        </div>
        <div class="col-md-2 col-sm-4 col-6 mb-3">
            <a href="{{ route('sales_reports.index') }}" class="btn btn-outline-danger btn-block py-3">
                <i class="fas fa-chart-bar fa-lg mb-1 d-block"></i>
                تقارير المبيعات
            </a>
        </div>
    </div>

</div>
@endsection
