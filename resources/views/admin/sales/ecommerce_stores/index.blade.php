@extends('admin.layouts.sales')
@section('title') ربط المتاجر الإلكترونية @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('sales_ecommerce_stores.index') }}">ربط المتاجر الإلكترونية</a> @endsection
@section('startpage') عرض الكل @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-store ml-2"></i> المتاجر الإلكترونية المرتبطة</h3>
            <div class="card-tools">
                @if($walletAccountId)
                    <a href="{{ route('accounting_reports.ledger', ['account_id' => $walletAccountId]) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-book"></i> حركة حساب المحفظة
                    </a>
                @endif
                <a href="{{ route('sales_ecommerce_stores.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> إضافة متجر
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success mx-3 mt-3">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning mx-3 mt-3" style="white-space:pre-line">{{ session('warning') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger mx-3 mt-3" style="white-space:pre-line">{{ session('error') }}</div>
            @endif
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>المنصة</th>
                        <th>Store ID</th>
                        <th>آخر مزامنة</th>
                        <th>نتيجة آخر مزامنة</th>
                        <th>رصيد المحفظة</th>
                        <th>الحالة</th>
                        <th style="width:220px">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stores as $store)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $store->name ?: '—' }}</strong></td>
                        <td><span class="badge badge-info">{{ $store->provider }}</span></td>
                        <td><code>{{ $store->store_id }}</code></td>
                        <td>{{ $store->last_synced_at ? $store->last_synced_at->format('Y/m/d H:i') : 'لم تتم بعد' }}</td>
                        <td>
                            @switch($store->last_sync_status)
                                @case('success') <span class="badge badge-success">{{ $store->last_sync_count }} طلب</span> @break
                                @case('failed')  <span class="badge badge-danger" title="{{ $store->last_sync_error }}">فشل</span> @break
                                @default         <span class="badge badge-secondary">لم تُختبر</span>
                            @endswitch
                        </td>
                        <td>
                            @if(!is_null($store->wallet_balance))
                                <strong>{{ number_format($store->wallet_balance, 2) }}</strong> ج.م
                                <small class="text-muted d-block">{{ $store->wallet_synced_at?->format('Y/m/d H:i') }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($store->is_active)
                                <span class="badge badge-success">مفعّل</span>
                            @else
                                <span class="badge badge-secondary">موقوف</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('sales_ecommerce_stores.sync', $store->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success" title="مزامنة الآن">
                                    <i class="fas fa-sync"></i> مزامنة الآن
                                </button>
                            </form>
                            <a href="{{ route('sales_ecommerce_stores.edit', $store->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('sales_ecommerce_stores.delete', $store->id) }}" class="btn btn-xs btn-danger" title="حذف"
                               onclick="return confirm('هل أنت متأكد من حذف هذا المتجر؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-store fa-2x mb-2 d-block"></i>
                            لا توجد متاجر إلكترونية مرتبطة بعد
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
