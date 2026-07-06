@extends('admin.layouts.manufacturing')
@section('title') أوامر الإنتاج @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('production_orders.index') }}">أوامر الإنتاج</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-industry ml-2"></i> أوامر الإنتاج</h3>
            <div class="card-tools">
                <a href="{{ route('production_orders.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> أمر إنتاج جديد</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الأمر</th><th>المنتج</th><th>الكمية المخططة</th><th>المُنتَج فعليًا</th><th>التكلفة الإجمالية</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->item->name ?? '-' }}</td>
                        <td>{{ number_format($order->planned_quantity, 2) }}</td>
                        <td>{{ number_format($order->produced_quantity, 2) }}</td>
                        <td>{{ number_format($order->total_cost, 2) }}</td>
                        <td><span class="badge badge-{{ $order->status_color }}">{{ $order->status_label }}</span></td>
                        <td><a href="{{ route('production_orders.show', $order->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد أوامر إنتاج</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
