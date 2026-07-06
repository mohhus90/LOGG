@extends('admin.layouts.manufacturing')
@section('title') ملخص تكاليف الإنتاج @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('manufacturing_reports.cost_summary') }}">ملخص التكاليف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar ml-2"></i> ملخص تكاليف الإنتاج</h3></div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الأمر</th><th>المنتج</th><th>مواد</th><th>عمالة</th><th>تكاليف غير مباشرة</th><th>الإجمالي</th><th>الكمية المنتَجة</th><th>تكلفة الوحدة</th><th>الحالة</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $order)
                    <tr>
                        <td><a href="{{ route('production_orders.show', $order->id) }}">{{ $order->order_number }}</a></td>
                        <td>{{ $order->item->name ?? '-' }}</td>
                        <td>{{ number_format($order->material_cost, 2) }}</td>
                        <td>{{ number_format($order->labor_cost, 2) }}</td>
                        <td>{{ number_format($order->overhead_cost, 2) }}</td>
                        <td>{{ number_format($order->total_cost, 2) }}</td>
                        <td>{{ number_format($order->produced_quantity, 2) }}</td>
                        <td>{{ $order->produced_quantity > 0 ? number_format($order->total_cost / $order->produced_quantity, 4) : '-' }}</td>
                        <td><span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">لا توجد أوامر إنتاج</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
