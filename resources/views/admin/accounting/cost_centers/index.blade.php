@extends('admin.layouts.accounting')
@section('title') مراكز التكلفة @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('cost_centers.index') }}">مراكز التكلفة</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-project-diagram ml-2"></i> مراكز التكلفة</h3>
            <div class="card-tools">
                <a href="{{ route('cost_centers.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>#</th><th>الكود</th><th>الاسم</th><th>المركز الأب</th><th>الفرع</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $center)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $center->code }}</td>
                        <td>{{ $center->name }}</td>
                        <td>{{ $center->parent->name ?? '-' }}</td>
                        <td>{{ $center->branch->name ?? '-' }}</td>
                        <td>
                            @if($center->is_active)<span class="badge badge-success">مفعّل</span>
                            @else<span class="badge badge-secondary">غير مفعّل</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('cost_centers.edit', $center->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('cost_centers.delete', $center->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف مركز التكلفة؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد مراكز تكلفة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
