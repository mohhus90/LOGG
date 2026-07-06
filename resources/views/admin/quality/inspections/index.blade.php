@extends('admin.layouts.quality')
@section('title') فحوصات الجودة @endsection
@section('start') ضبط الجودة @endsection
@section('home') <a href="{{ route('quality_inspections.index') }}">فحوصات الجودة</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-search ml-2"></i> فحوصات الجودة</h3>
            <div class="card-tools">
                <a href="{{ route('quality_inspections.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> فحص جديد</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الفحص</th><th>القالب</th><th>المصدر</th><th>التاريخ</th><th>النتيجة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $inspection)
                    <tr>
                        <td>{{ $inspection->inspection_number }}</td>
                        <td>{{ $inspection->checklist->name ?? '-' }}</td>
                        <td>{{ $inspection->source_type_label }}</td>
                        <td>{{ \Carbon\Carbon::parse($inspection->date)->format('Y-m-d') }}</td>
                        <td><span class="badge badge-{{ $inspection->result_color }}">{{ $inspection->result_label }}</span></td>
                        <td>
                            <a href="{{ route('quality_inspections.show', $inspection->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('quality_inspections.delete', $inspection->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف الفحص؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد فحوصات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
