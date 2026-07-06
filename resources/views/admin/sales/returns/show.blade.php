@extends('admin.layouts.sales')
@section('title') تفاصيل المرتجع @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_returns.index') }}">المرتجعات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-outline
        @switch($return->status)
            @case('approved') card-success @break
            @case('rejected') card-danger @break
            @default card-secondary
        @endswitch
    ">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-undo ml-2"></i>
                مرتجع #{{ $return->return_number ?? $return->id }}
            </h3>
            {{-- Status Badge (prominent) --}}
            <span class="mr-3" style="font-size:1.1em;">
                @switch($return->status)
                    @case('draft')
                        <span class="badge badge-secondary badge-lg px-3 py-2">
                            <i class="fas fa-pencil-alt ml-1"></i> مسودة
                        </span>
                        @break
                    @case('approved')
                        <span class="badge badge-success badge-lg px-3 py-2">
                            <i class="fas fa-check-circle ml-1"></i> معتمد
                        </span>
                        @break
                    @case('rejected')
                        <span class="badge badge-danger badge-lg px-3 py-2">
                            <i class="fas fa-times-circle ml-1"></i> مرفوض
                        </span>
                        @break
                @endswitch
            </span>
            <div class="card-tools">
                <a href="{{ route('sales_returns.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-right ml-1"></i> رجوع
                </a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Header Info --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <th class="bg-light" width="40%">رقم المرتجع</th>
                            <td><strong class="text-primary">#{{ $return->return_number ?? $return->id }}</strong></td>
                        </tr>
                        <tr>
                            <th class="bg-light">التاريخ</th>
                            <td>{{ \Carbon\Carbon::parse($return->date)->format('Y/m/d') }}</td>
                        </tr>
                        <tr>
                            <th class="bg-light">العميل</th>
                            <td>
                                <strong>{{ $return->customer->name ?? '-' }}</strong>
                                @if($return->customer->phone ?? null)
                                    <br><small class="text-muted"><i class="fas fa-phone ml-1"></i>{{ $return->customer->phone }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">الفاتورة المرتجع منها</th>
                            <td>
                                @if($return->invoice)
                                    <a href="{{ route('sales_invoices.show', $return->invoice_id) }}">
                                        #{{ $return->invoice->invoice_number ?? $return->invoice_id }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    {{-- Reason Card --}}
                    <div class="card bg-light h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold mb-2"><i class="fas fa-comment-alt ml-2 text-warning"></i>سبب الإرجاع</h6>
                            <p class="mb-0">{{ $return->reason ?? '-' }}</p>
                            @if($return->notes)
                                <hr>
                                <h6 class="font-weight-bold mb-2"><i class="fas fa-sticky-note ml-2 text-info"></i>ملاحظات</h6>
                                <p class="mb-0">{{ $return->notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
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
                                @forelse($return->items ?? [] as $i => $item)
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
                                    <td><strong>{{ number_format($return->subtotal ?? 0, 2) }} ج.م</strong></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-left font-weight-bold">ضريبة القيمة المضافة (14%)</td>
                                    <td><strong>{{ number_format($return->tax_amount ?? 0, 2) }} ج.م</strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="6" class="text-left font-weight-bold">الإجمالي الكلي</td>
                                    <td><strong style="font-size:1.1em">{{ number_format($return->total ?? 0, 2) }} ج.م</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <a href="{{ route('sales_returns.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-right ml-1"></i> رجوع للقائمة
            </a>

            @if($return->status === 'draft')
                <form action="{{ route('sales_returns.approve', $return->id) }}" method="POST" class="d-inline mr-2">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('اعتماد هذا المرتجع؟')">
                        <i class="fas fa-check ml-1"></i> اعتماد
                    </button>
                </form>
                <form action="{{ route('sales_returns.reject', $return->id) }}" method="POST" class="d-inline mr-2">
                    @csrf
                    <button type="submit" class="btn btn-warning" onclick="return confirm('رفض هذا المرتجع؟')">
                        <i class="fas fa-ban ml-1"></i> رفض
                    </button>
                </form>
            @endif

            <a href="{{ route('sales_returns.delete', $return->id) }}" class="btn btn-danger mr-2"
               onclick="return confirm('هل أنت متأكد من حذف هذا المرتجع؟')">
                <i class="fas fa-trash ml-1"></i> حذف
            </a>
        </div>
    </div>
</div>
@endsection
