@extends('admin.layouts.sales')
@section('title') ماكينات الكاشير @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('pos_registers.index') }}">ماكينات الكاشير</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-store ml-2"></i> ماكينات الكاشير</h3>
            <div class="card-tools">
                <a href="{{ route('pos_registers.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة كاشير</a>
            </div>
        </div>
        @if(session('success'))
          <div class="alert alert-success m-3 alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
          </div>
        @endif
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>#</th><th>الاسم</th><th>الخزنة</th><th>المخزن</th><th>الفرع</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($registers as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->cashBox->name ?? '-' }}</td>
                        <td>{{ $r->warehouse->name ?? '-' }}</td>
                        <td>{{ $r->branch->name ?? '-' }}</td>
                        <td>
                            @if($r->is_active)<span class="badge badge-success">مفعّل</span>
                            @else<span class="badge badge-secondary">معطّل</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('pos_registers.edit', $r->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('pos.terminal', $r->id) }}" class="btn btn-xs btn-success"><i class="fas fa-cash-register"></i> فتح</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد كاشيرات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $registers->links() }}</div>
    </div>
</div>
@endsection
