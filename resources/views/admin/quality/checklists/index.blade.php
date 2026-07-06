@extends('admin.layouts.quality')
@section('title') قوالب الفحص @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_checklists.index') }}">قوالب الفحص</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-list ml-2"></i> قوالب فحص الجودة</h3>
            <div class="card-tools">
                <a href="{{ route('quality_checklists.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> قالب جديد</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>الاسم</th><th>يُطبَّق على</th><th>عدد البنود</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $checklist)
                    <tr>
                        <td>{{ $checklist->name }}</td>
                        <td>{{ $checklist->applies_to_label }}</td>
                        <td>{{ $checklist->items_count }}</td>
                        <td>
                            @if($checklist->is_active)<span class="badge badge-success">مفعّل</span>
                            @else<span class="badge badge-secondary">غير مفعّل</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('quality_checklists.show', $checklist->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('quality_checklists.edit', $checklist->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('quality_checklists.delete', $checklist->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف القالب؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد قوالب فحص</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
