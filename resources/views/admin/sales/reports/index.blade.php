@extends('admin.layouts.sales')
@section('title') تقارير المبيعات @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_reports.index') }}">التقارير</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    {{-- 6 Stat Boxes (Row 1) --}}
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_sales'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>إجمالي المبيعات</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <a href="{{ route('sales_reports.summary') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['total_collected'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>إجمالي المحصّل</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <a href="{{ route('sales_payments.index') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['total_debt'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>إجمالي الديون</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('sales_reports.debt') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Row 2 --}}
    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['invoices_count'] ?? 0) }}</h3>
                    <p>عدد الفواتير</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
                <a href="{{ route('sales_invoices.index') }}" class="small-box-footer">
                    عرض الفواتير <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['orders_count'] ?? 0) }}</h3>
                    <p>عدد الأوامر</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                <a href="{{ route('sales_orders.index') }}" class="small-box-footer">
                    عرض الأوامر <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($stats['customers_count'] ?? 0) }}</h3>
                    <p>عدد العملاء</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('sales_customers.index') }}" class="small-box-footer">
                    عرض العملاء <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Monthly Sales Bar Chart --}}
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar ml-2"></i> المبيعات الشهرية (آخر 6 أشهر)</h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlySalesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Top 5 Customers --}}
        <div class="col-md-4">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trophy ml-2 text-warning"></i> أعلى 5 عملاء</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>العميل</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers ?? [] as $i => $customer)
                            <tr>
                                <td>
                                    @if($i == 0) <i class="fas fa-trophy text-warning"></i>
                                    @elseif($i == 1) <i class="fas fa-trophy text-secondary"></i>
                                    @elseif($i == 2) <i class="fas fa-trophy text-danger" style="opacity:.6"></i>
                                    @else {{ $i + 1 }}
                                    @endif
                                </td>
                                <td>{{ $customer->name ?? $customer->customer_name ?? '-' }}</td>
                                <td><strong>{{ number_format($customer->total_sales ?? $customer->total ?? 0, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('sales_reports.customer') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-list ml-1"></i> تقرير بالعميل
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Cards --}}
    <div class="row">
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('sales_reports.summary') }}" class="btn btn-block btn-outline-primary py-3">
                        <i class="fas fa-file-alt fa-2x d-block mb-2"></i>
                        <strong>تقرير ملخص</strong>
                        <br><small class="text-muted">جميع الفواتير بالتفاصيل</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('sales_reports.customer') }}" class="btn btn-block btn-outline-success py-3">
                        <i class="fas fa-user-chart fa-2x d-block mb-2"></i>
                        <strong>تقرير بالعميل</strong>
                        <br><small class="text-muted">مبيعات لكل عميل</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('sales_reports.item') }}" class="btn btn-block btn-outline-info py-3">
                        <i class="fas fa-boxes fa-2x d-block mb-2"></i>
                        <strong>تقرير بالصنف</strong>
                        <br><small class="text-muted">مبيعات لكل صنف</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('sales_reports.debt') }}" class="btn btn-block btn-outline-danger py-3">
                        <i class="fas fa-hand-holding-usd fa-2x d-block mb-2"></i>
                        <strong>تقرير الديون</strong>
                        <br><small class="text-muted">الفواتير غير المسددة</small>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    var ctx = document.getElementById('monthlySalesChart').getContext('2d');
    var monthlySales = @json($monthlySales ?? []);
    var labels = monthlySales.map(function(m) { return m.month_label || m.month; });
    var values = monthlySales.map(function(m) { return parseFloat(m.total || 0); });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'المبيعات (ج.م)',
                data: values,
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + parseFloat(ctx.raw).toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(val) {
                            return val.toLocaleString('ar-EG') + ' ج.م';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
