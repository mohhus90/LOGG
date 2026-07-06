@extends('admin.layouts.purchasing')
@section('title') تقارير المشتريات @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_reports.index') }}">التقارير</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['total_invoiced'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>إجمالي المشتريات</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <a href="{{ route('purchase_reports.summary') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($stats['total_paid'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>إجمالي المدفوع</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <a href="{{ route('purchase_payments.index') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ number_format($stats['total_debt'] ?? 0, 2) }}<small style="font-size:13px"> ج.م</small></h3>
                    <p>إجمالي المستحقات</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('purchase_reports.debt') }}" class="small-box-footer">
                    التفاصيل <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($stats['invoices_count'] ?? 0) }}</h3>
                    <p>عدد الفواتير</p>
                </div>
                <div class="icon"><i class="fas fa-file-invoice"></i></div>
                <a href="{{ route('purchase_invoices.index') }}" class="small-box-footer">
                    عرض الفواتير <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($stats['orders_count'] ?? 0) }}</h3>
                    <p>عدد أوامر الشراء</p>
                </div>
                <div class="icon"><i class="fas fa-file-signature"></i></div>
                <a href="{{ route('purchase_orders.index') }}" class="small-box-footer">
                    عرض الأوامر <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($stats['suppliers_count'] ?? 0) }}</h3>
                    <p>عدد الموردين</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
                <a href="{{ route('suppliers.index') }}" class="small-box-footer">
                    عرض الموردين <i class="fas fa-arrow-circle-left"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar ml-2"></i> المشتريات الشهرية (آخر 6 أشهر)</h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyPurchasesChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trophy ml-2 text-warning"></i> أعلى 5 موردين</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>المورد</th>
                                <th>الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSuppliers ?? [] as $i => $supplier)
                            <tr>
                                <td>
                                    @if($i == 0) <i class="fas fa-trophy text-warning"></i>
                                    @elseif($i == 1) <i class="fas fa-trophy text-secondary"></i>
                                    @elseif($i == 2) <i class="fas fa-trophy text-danger" style="opacity:.6"></i>
                                    @else {{ $i + 1 }}
                                    @endif
                                </td>
                                <td>{{ $supplier->name ?? '-' }}</td>
                                <td><strong>{{ number_format($supplier->total_purchases ?? 0, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted">لا توجد بيانات</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('purchase_reports.supplier') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-list ml-1"></i> تقرير بالمورد
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('purchase_reports.summary') }}" class="btn btn-block btn-outline-primary py-3">
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
                    <a href="{{ route('purchase_reports.supplier') }}" class="btn btn-block btn-outline-success py-3">
                        <i class="fas fa-truck fa-2x d-block mb-2"></i>
                        <strong>تقرير بالمورد</strong>
                        <br><small class="text-muted">مشتريات لكل مورد</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('purchase_reports.item') }}" class="btn btn-block btn-outline-info py-3">
                        <i class="fas fa-boxes fa-2x d-block mb-2"></i>
                        <strong>تقرير بالصنف</strong>
                        <br><small class="text-muted">مشتريات لكل صنف</small>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-widget widget-user-2 shadow">
                <div class="card-footer p-0">
                    <a href="{{ route('purchase_reports.debt') }}" class="btn btn-block btn-outline-danger py-3">
                        <i class="fas fa-hand-holding-usd fa-2x d-block mb-2"></i>
                        <strong>تقرير المستحقات</strong>
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
    var ctx = document.getElementById('monthlyPurchasesChart').getContext('2d');
    var monthlyPurchases = @json($monthlyPurchases ?? []);
    var labels = monthlyPurchases.map(function(m) { return m.mn + '/' + m.yr; });
    var values = monthlyPurchases.map(function(m) { return parseFloat(m.total_amount || 0); });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'المشتريات (ج.م)',
                data: values,
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderColor: 'rgba(139, 92, 246, 1)',
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
