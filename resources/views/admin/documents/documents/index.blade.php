@extends('admin.layouts.documents')
@section('title') الوثائق @endsection
@section('start') إدارة الوثائق @endsection
@section('home') <a href="{{ route('documents.index') }}">الوثائق</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-alt ml-2"></i> الوثائق</h3>
            <div class="card-tools">
                <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i> رفع وثيقة جديدة</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="category_id" class="form-control form-control-sm">
                        <option value="">كل الفئات</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">كل الحالات</option>
                        <option value="draft" {{ request('status')=='draft'?'selected':'' }}>مسودة</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>قيد المراجعة</option>
                        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>معتمدة</option>
                        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>مرفوضة</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> فلترة</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>العنوان</th><th>الفئة</th><th>الملف</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $document)
                    <tr>
                        <td>{{ $document->title }}</td>
                        <td>{{ $document->category->name ?? '-' }}</td>
                        <td>{{ $document->file_original_name }}</td>
                        <td><span class="badge badge-{{ $document->status_color }}">{{ $document->status_label }}</span></td>
                        <td>
                            <a href="{{ route('documents.show', $document->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('documents.download', $document->id) }}" class="btn btn-xs btn-secondary"><i class="fas fa-download"></i></a>
                            <a href="{{ route('documents.delete', $document->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف الوثيقة؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد وثائق</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
