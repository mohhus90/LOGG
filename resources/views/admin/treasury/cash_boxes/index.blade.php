@extends('admin.layouts.treasury')
@section('title') الخزائن النقدية @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('cash_boxes.index') }}">الخزائن النقدية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cash-register ml-2"></i> الخزائن النقدية</h3>
            <div class="card-tools">
                <a href="{{ route('cash_boxes.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة خزنة</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>الكود</th><th>الاسم</th><th>الفرع</th><th>الحساب المحاسبي</th><th>الرصيد الحالي</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $box)
                    <tr>
                        <td>{{ $box->code }}</td>
                        <td>{{ $box->name }}</td>
                        <td>{{ $box->branch->name ?? '-' }}</td>
                        <td>{{ $box->glAccount->account_name ?? '-' }}</td>
                        <td>{{ number_format($box->current_balance, 2) }}</td>
                        <td>
                            @if($box->is_active)<span class="badge badge-success">مفعّل</span>
                            @else<span class="badge badge-secondary">غير مفعّل</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('cash_boxes.edit', $box->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('cash_boxes.delete', $box->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف الخزنة؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد خزائن مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
