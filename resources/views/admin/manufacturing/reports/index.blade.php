@extends('admin.layouts.manufacturing')
@section('title') لوحة الإنتاج @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('manufacturing_reports.index') }}">لوحة الإنتاج</a> @endsection
@section('startpage') الرئيسية @endsection

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner"><h3>{{ $stats['draft'] }}</h3><p>أوامر مسودة</p></div>
                <div class="icon"><i class="fas fa-file-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $stats['in_progress'] }}</h3><p>قيد التنفيذ</p></div>
                <div class="icon"><i class="fas fa-cogs"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $stats['completed'] }}</h3><p>أوامر مكتملة</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner"><h3>{{ number_format($stats['total_cost'], 0) }}</h3><p>تكلفة الإنتاج المكتمل</p></div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-header"><h3 class="card-title">آخر أوامر الإنتاج</h3></div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead><tr><th>رقم الأمر</th><th>المنتج</th><th>الحالة</th></tr></thead>
                <tbody>
                    @forelse($recent as $order)
                    <tr>
                        <td><a href="{{ route('production_orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->item->name ?? '-' }}</td>
                        <td><span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">لا توجد أوامر إنتاج</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
