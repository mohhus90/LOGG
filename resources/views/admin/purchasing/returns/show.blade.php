@extends('admin.layouts.purchasing')
@section('title') تفاصيل المرتجع @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_returns.index') }}">المرتجعات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-outline
        @switch($ret->status)
            @case('approved') card-success @break
            @case('rejected') card-danger @break
            @default card-secondary
        @endswitch
    ">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-undo ml-2"></i>
                مرتجع #{{ $ret->return_number ?? $ret->id }}
            </h3>
            <span class="mr-3" style="font-size:1.1em;">{!! $ret->status_label !!}</span>
            <div class="card-tools">
                <a href="{{ route('purchase_returns.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" width="40%">رقم المرتجع</th>
                            <td><strong class="text-primary">#{{ $ret->return_number ?? $ret->id }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">التاريخ</th>
                            <td>{{ \Carbon\Carbon::parse($ret->date)->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">المورد</th>
                            <td>
                                <strong>{{ $ret->supplier->name ?? '-' }}</strong>
                                @if($ret->supplier->phone ?? null)
                                    <br><small class="text-muted"><i class="fas fa-phone ml-1"></i>{{ $ret->supplier->phone }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">الفاتورة المرتجع منها</th>
                            <td>
                                @if($ret->invoice)
                                    <a href="{{ route('purchase_invoices.show', $ret->invoice_id) }}">
                                        #{{ $ret->invoice->invoice_number ?? $ret->invoice_id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-2"><i class="fas fa-comment-alt ml-2 text-warning"></i>سبب الإرجاع</h6>
                            <p class="mb-0">{{ $ret->reason ?? '-' }}</p>
                            @if($ret->notes)
                                <hr>
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-sticky-note ml-2 text-info"></i>ملاحظات</h6>
                                <p class="mb-0">{{ $ret->notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-light">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0"><i class="fas fa-list ml-2"></i>أصناف المرتجع</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>الصنف</th>
                                    <th>البيان</th>
                                    <th>الوحدة</th>
                                    <th>الكمية</th>
                                    <th>السعر</th>
                                    <th>الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ret->items ?? [] as $i => $item)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td><strong>{{ $item->item->name ?? '-' }}</strong></td>
                                    <td>{{ $item->description ?? '-' }}</td>
                                    <td>{{ $item->unit->name ?? '-' }}</td>
                                    <td>{{ number_format($item->quantity ?? 0, 2) }}</td>
                                    <td>{{ number_format($item->unit_price ?? 0, 2) }} ج.م</td>
                                    <td><strong>{{ number_format($item->total ?? (($item->quantity ?? 0) * ($item->unit_price ?? 0)), 2) }} ج.م</strong></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">لا توجد أصناف</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="6" class="text-left font-weight-bold">الإجمالي الفرعي</td>
                                    <td><strong>{{ number_format($ret->subtotal ?? 0, 2) }} ج.م</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-left font-weight-bold">ضريبة القيمة المضافة (14%)</td>
                                    <td><strong>{{ number_format($ret->tax_amount ?? 0, 2) }} ج.م</strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="6" class="text-left font-weight-bold">الإجمالي الكلي</td>
                                    <td><strong style="font-size:1.1em">{{ number_format($ret->total ?? 0, 2) }} ج.م</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('purchase_returns.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right ml-1"></i> رجوع للقائمة
            </a>

            @if($ret->status === 'draft')
                <form action="{{ route('purchase_returns.approve', $ret->id) }}" method="POST" class="d-inline mr-2">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('اعتماد هذا المرتجع؟')">
                        <i class="fas fa-check ml-1"></i> اعتماد
                    </button>
                </form>
                <form action="{{ route('purchase_returns.reject', $ret->id) }}" method="POST" class="d-inline mr-2">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('رفض هذا المرتجع؟')">
                        <i class="fas fa-ban ml-1"></i> رفض
                    </button>
                </form>
            @endif

            <a href="{{ route('purchase_returns.delete', $ret->id) }}" class="btn btn-danger mr-2"
               onclick="return confirm('هل أنت متأكد من حذف هذا المرتجع؟')">
                <i class="fas fa-trash ml-1"></i> حذف
            </a>
        </div>
    </div>
</div>
@endsection
