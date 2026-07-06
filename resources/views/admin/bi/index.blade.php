@extends('admin.layouts.bi')
@section('title') اللوحة التنفيذية @endsection
@section('start') التقارير والتحليلات @endsection
@section('home') <a href="{{ route('bi_dashboard.index') }}">اللوحة التنفيذية</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ number_format($sales['this_month'], 0) }}</h3><p>مبيعات الشهر الحالي</p></div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ number_format($purchases['this_month'], 0) }}</h3><p>مشتريات الشهر الحالي</p></div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner"><h3>{{ number_format($netProfit, 0) }}</h3><p>{{ $netProfit >= 0 ? 'صافي الربح' : 'صافي الخسارة' }} (هذا العام)</p></div>
                <div class="icon"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ number_format($inventoryValue, 0) }}</h3><p>قيمة المخزون الحالية</p></div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ number_format($treasury['cash'] + $treasury['bank'], 0) }}</h3><p>إجمالي السيولة (نقدية+بنوك)</p></div>
                <div class="icon"><i class="fas fa-cash-register"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-danger">
                <div class="inner"><h3>{{ number_format($sales['receivable'], 0) }}</h3><p>ذمم مدينة (عملاء)</p></div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ number_format($purchases['payable'], 0) }}</h3><p>ذمم دائنة (موردون)</p></div>
                <div class="icon"><i class="fas fa-money-check"></i></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="small-box bg-dark">
                <div class="inner"><h3>{{ $employeesCount }}</h3><p>الموظفون النشطون</p></div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header"><h3 class="card-title">اتجاه المبيعات (آخر 6 أشهر)</h3></div>
                <div class="card-body"><canvas id="salesTrendChart" style="max-height:320px"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header"><h3 class="card-title">ملخص العام حتى تاريخه</h3></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span>الإيرادات:</span><strong>{{ number_format($revenueTotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>المصروفات:</span><strong>{{ number_format($expenseTotal, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>رواتب هذا الشهر:</span><strong>{{ number_format($payrollThisMonth, 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between"><strong>{{ $netProfit >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}:</strong>
                        <strong class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($netProfit, 2) }}</strong>
                    </div>
                    <a href="{{ route('accounting_reports.income_statement') }}" class="btn btn-sm btn-outline-primary btn-block mt-3">قائمة الدخل التفصيلية</a>
                    <a href="{{ route('accounting_reports.balance_sheet') }}" class="btn btn-sm btn-outline-secondary btn-block mt-2">الميزانية العمومية</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
new Chart(document.getElementById('salesTrendChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: @json(collect($salesTrend)->pluck('label')),
        datasets: [{
            label: 'إجمالي المبيعات',
            data: @json(collect($salesTrend)->pluck('total')),
            backgroundColor: 'rgba(52,211,153,.6)',
        }]
    },
    options: { scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
});
</script>
@endsection
