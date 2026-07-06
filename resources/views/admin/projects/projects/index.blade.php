@extends('admin.layouts.projects')
@section('title') المشاريع @endsection
@section('start') إدارة المشاريع @endsection
@section('home') <a href="{{ route('projects.index') }}">المشاريع</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-project-diagram ml-2"></i> المشاريع</h3>
            <div class="card-tools">
                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> مشروع جديد</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">كل الحالات</option>
                        @foreach(\App\Models\Project::statusOptions() as $key => [$label, $color])
                            <option value="{{ $key }}" {{ request('status')==$key?'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> فلترة</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>الاسم</th><th>العميل</th><th>تاريخ البدء</th><th>تاريخ الانتهاء</th><th>الميزانية</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $project)
                    <tr>
                        <td>{{ $project->name }}</td>
                        <td>{{ $project->customer->name ?? '-' }}</td>
                        <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '-' }}</td>
                        <td>{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '-' }}</td>
                        <td>{{ number_format($project->budget, 2) }}</td>
                        <td><span class="badge badge-{{ $project->status_color }}">{{ $project->status_label }}</span></td>
                        <td>
                            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('projects.delete', $project->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف المشروع؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد مشاريع مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
