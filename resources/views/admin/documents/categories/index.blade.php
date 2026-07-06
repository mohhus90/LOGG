@extends('admin.layouts.documents')
@section('title') فئات الوثائق @endsection
@section('start') إدارة الوثائق @endsection
@section('home') <a href="{{ route('document_categories.index') }}">فئات الوثائق</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-folder ml-2"></i> فئات الوثائق</h3></div>
        <div class="card-body">
            <form action="{{ route('document_categories.store') }}" method="POST" class="form-inline mb-3">
                @csrf
                <input type="text" name="name" class="form-control ml-2" placeholder="اسم الفئة الجديدة" required>
                <button class="btn btn-primary"><i class="fas fa-plus"></i> إضافة</button>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-dark"><tr><th>الاسم</th><th>عدد الوثائق</th><th>الحالة</th><th>إجراء</th></tr></thead>
                <tbody>
                    @forelse($data as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->documents_count }}</td>
                        <td>
                            @if($category->is_active)<span class="badge badge-success">مفعّلة</span>
                            @else<span class="badge badge-secondary">غير مفعّلة</span>@endif
                        </td>
                        <td>
                            <form action="{{ route('document_categories.update', $category->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="name" value="{{ $category->name }}">
                                <input type="hidden" name="is_active" value="{{ $category->is_active ? 0 : 1 }}">
                                <button class="btn btn-xs btn-warning">{{ $category->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                            </form>
                            <a href="{{ route('document_categories.delete', $category->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف الفئة؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">لا توجد فئات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
