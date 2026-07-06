@extends('admin.layouts.manufacturing')
@section('title') أمر إنتاج {{ $order->order_number }} @endsection
@section('start') الإنتاج @endsection
@section('home') <a href="{{ route('production_orders.index') }}">أوامر الإنتاج</a> @endsection
@section('startpage') {{ $order->order_number }} @endsection

@section('content')
<div class="col-lg-10">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-industry ml-2"></i> أمر إنتاج {{ $order->order_number }}
                <span class="badge badge-{{ $order->status_color }} mr-2">{{ $order->status_label }}</span>
            </h3>
            <div class="card-tools">
                @if($order->status !== 'completed' && $order->status !== 'cancelled')
                <form action="{{ route('production_orders.complete', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('إغلاق أمر الإنتاج؟')">
                    @csrf<button class="btn btn-sm btn-success"><i class="fas fa-check"></i> إغلاق الأمر</button>
                </form>
                <form action="{{ route('production_orders.cancel', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('إلغاء أمر الإنتاج؟')">
                    @csrf<button class="btn btn-sm btn-danger"><i class="fas fa-times"></i> إلغاء</button>
                </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>المنتج:</strong> {{ $order->item->name ?? '-' }}</div>
                <div class="col-md-3"><strong>الكمية المخططة:</strong> {{ number_format($order->planned_quantity, 2) }}</div>
                <div class="col-md-3"><strong>المُنتَج فعليًا:</strong> {{ number_format($order->produced_quantity, 2) }}</div>
                <div class="col-md-3"><strong>مخزن الخام:</strong> {{ $order->sourceWarehouse->name ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>مخزن التام:</strong> {{ $order->targetWarehouse->name ?? '-' }}</div>
                <div class="col-md-3 mt-2"><strong>تكلفة المواد:</strong> {{ number_format($order->material_cost, 2) }}</div>
                <div class="col-md-3 mt-2"><strong>تكلفة العمالة:</strong> {{ number_format($order->labor_cost, 2) }}</div>
                <div class="col-md-3 mt-2"><strong>التكلفة الإجمالية:</strong> {{ number_format($order->total_cost, 2) }}</div>
            </div>

            <hr>
            <h5>مواد التشغيل</h5>
            <form action="{{ route('production_orders.issue_materials', $order->id) }}" method="POST">
                @csrf
                <table class="table table-bordered table-sm">
                    <thead class="thead-dark">
                        <tr><th>المكوّن</th><th>الكمية المخططة</th><th>تم صرفه</th><th>تكلفة الوحدة</th><th style="width:160px">صرف كمية إضافية</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->materials as $material)
                        <tr>
                            <td>{{ $material->item->name ?? '-' }}</td>
                            <td>{{ number_format($material->planned_quantity, 4) }}</td>
                            <td>{{ number_format($material->issued_quantity, 4) }}</td>
                            <td>{{ number_format($material->unit_cost ?? 0, 4) }}</td>
                            <td>
                                @if($order->status !== 'completed' && $order->status !== 'cancelled')
                                <input type="number" step="0.0001" name="materials[{{ $material->id }}]" class="form-control form-control-sm" placeholder="0">
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($order->status !== 'completed' && $order->status !== 'cancelled')
                <button class="btn btn-sm btn-warning"><i class="fas fa-dolly ml-1"></i> صرف المواد المُدخلة</button>
                @endif
            </form>

            <hr>
            <h5>استلام الإنتاج التام</h5>
            @if($order->status !== 'completed' && $order->status !== 'cancelled')
            <form action="{{ route('production_orders.receive', $order->id) }}" method="POST" class="form-inline mb-3">
                @csrf
                <input type="number" step="0.0001" name="quantity" class="form-control ml-2" placeholder="الكمية المستلمة" required>
                <button class="btn btn-success"><i class="fas fa-check-circle ml-1"></i> استلام</button>
            </form>
            @endif
            <table class="table table-bordered table-sm">
                <thead class="thead-dark"><tr><th>التاريخ</th><th>الكمية</th><th>تكلفة الوحدة</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    @forelse($order->receipts as $receipt)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($receipt->date)->format('Y-m-d') }}</td>
                        <td>{{ number_format($receipt->quantity, 4) }}</td>
                        <td>{{ number_format($receipt->unit_cost, 4) }}</td>
                        <td>{{ number_format($receipt->total_cost, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">لا يوجد استلام بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
