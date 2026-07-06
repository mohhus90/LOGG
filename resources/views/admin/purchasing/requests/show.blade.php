@extends('admin.layouts.purchasing')
@section('title') طلب شراء {{ $req->request_number }} @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_requests.index') }}">طلبات الشراء</a> @endsection
@section('startpage') عرض التفاصيل @endsection

@section('content')
<div class="col-12">

    <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap" style="gap:8px">
        <div>
            @if($req->status === 'draft')
            <a href="{{ route('purchase_requests.edit', $req->id) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            <form action="{{ route('purchase_requests.status', $req->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="submitted">
                <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-paper-plane ml-1"></i> تقديم الطلب</button>
            </form>
            @endif
            @if($req->status === 'submitted')
            <form action="{{ route('purchase_requests.status', $req->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="approved">
                <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check ml-1"></i> اعتماد</button>
            </form>
            <form action="{{ route('purchase_requests.status', $req->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-ban ml-1"></i> رفض</button>
            </form>
            @endif
            @if($req->status === 'approved')
            <form action="{{ route('purchase_requests.convert', $req->id) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('تحويل هذا الطلب إلى أمر شراء؟')">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-file-signature ml-1"></i> تحويل إلى أمر شراء</button>
            </form>
            @endif
        </div>
        <div>{!! $req->status_label !!}</div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-clipboard-list ml-2"></i> بيانات الطلب</h3></div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><th style="width:140px" class="text-muted">رقم الطلب</th><td><strong>{{ $req->request_number }}</strong></td></tr>
                        <tr><th class="text-muted">التاريخ</th><td>{{ \Carbon\Carbon::parse($req->date)->format('Y/m/d') }}</td></tr>
                        <tr><th class="text-muted">مطلوب بتاريخ</th><td>{{ $req->needed_by_date ? \Carbon\Carbon::parse($req->needed_by_date)->format('Y/m/d') : '—' }}</td></tr>
                        <tr><th class="text-muted">الفرع</th><td>{{ $req->branch->name ?? '—' }}</td></tr>
                        <tr><th class="text-muted">أنشئ بواسطة</th><td>{{ $req->createdBy->name ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if($req->notes)
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-sticky-note ml-2"></i> ملاحظات</h3></div>
                <div class="card-body"><p class="text-muted mb-0">{{ $req->notes }}</p></div>
            </div>
            @endif
        </div>
    </div>

    <div class="card card-outline card-secondary">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list ml-2"></i> بنود الطلب</h3></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الصنف</th>
                            <th>الوصف</th>
                            <th>الوحدة</th>
                            <th class="text-center">الكمية</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($req->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $item->item->name ?? '—' }}</strong></td>
                            <td>{{ $item->description ?? '—' }}</td>
                            <td>{{ $item->unit->name ?? '—' }}</td>
                            <td class="text-center">{{ number_format($item->quantity, 3) }}</td>
                            <td>{{ $item->notes ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-3">لا توجد بنود</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
