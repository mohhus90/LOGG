@extends('admin.layouts.purchasing')
@section('title') طلبات الشراء @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('purchase_requests.index') }}">طلبات الشراء</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-clipboard-list ml-2"></i>
                طلبات الشراء
                <a href="{{ route('purchase_requests.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> طلب شراء جديد
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('purchase_requests.index') }}" class="form-inline mb-3 flex-wrap">
                <select name="status" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>مسودة</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>مقدم</option>
                    <option value="approved"  {{ request('status') == 'approved'  ? 'selected' : '' }}>معتمد</option>
                    <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>مرفوض</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>تم التحويل</option>
                </select>
                <input type="date" name="from" class="form-control ml-2 mb-1" value="{{ request('from') }}">
                <input type="date" name="to" class="form-control ml-2 mb-1" value="{{ request('to') }}">
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('purchase_requests.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>رقم الطلب</th>
                            <th>التاريخ</th>
                            <th>مطلوب بتاريخ</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $req)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $req->request_number }}</strong></td>
                            <td>{{ optional($req->date)->format('Y-m-d') }}</td>
                            <td>{{ optional($req->needed_by_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>{!! $req->status_label !!}</td>
                            <td>
                                <a href="{{ route('purchase_requests.show', $req->id) }}" class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($req->status === 'draft')
                                <a href="{{ route('purchase_requests.edit', $req->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                <a href="{{ route('purchase_requests.delete', $req->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذا الطلب؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2 d-block"></i>
                                لا توجد طلبات شراء
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
