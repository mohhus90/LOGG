@extends('admin.layouts.projects')
@section('title') {{ $project->name }} @endsection
@section('start') إدارة المشاريع @endsection
@section('home') <a href="{{ route('projects.index') }}">المشاريع</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-project-diagram ml-2"></i> {{ $project->name }}
                <span class="badge badge-{{ $project->status_color }} mr-2">{{ $project->status_label }}</span>
            </h3>
            <div class="card-tools">
                <a href="{{ route('projects.edit', $project->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> تعديل</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3"><strong>العميل:</strong> {{ $project->customer->name ?? '-' }}</div>
                <div class="col-md-3"><strong>تاريخ البدء:</strong> {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '-' }}</div>
                <div class="col-md-3"><strong>تاريخ الانتهاء:</strong> {{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '-' }}</div>
                <div class="col-md-3"><strong>الميزانية:</strong> {{ number_format($project->budget, 2) }}</div>
            </div>
            @if($project->notes)<p class="mt-2"><strong>ملاحظات:</strong> {{ $project->notes }}</p>@endif

            <hr>
            <h5>إضافة مهمة جديدة</h5>
            <form action="{{ route('project_tasks.store', $project->id) }}" method="POST" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-4"><input type="text" name="title" class="form-control form-control-sm" placeholder="عنوان المهمة" required></div>
                    <div class="col-md-3">
                        <select name="assigned_to" class="form-control form-control-sm">
                            <option value="">-- غير مُسنَد --</option>
                            @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->employee_name_A }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><input type="date" name="due_date" class="form-control form-control-sm"></div>
                    <div class="col-md-2">
                        <select name="priority" class="form-control form-control-sm">
                            <option value="low">منخفضة</option>
                            <option value="medium" selected>متوسطة</option>
                            <option value="high">عالية</option>
                        </select>
                    </div>
                    <div class="col-md-1"><button class="btn btn-sm btn-primary btn-block"><i class="fas fa-plus"></i></button></div>
                </div>
            </form>

            <div class="row">
                @foreach($statuses as $statusKey => [$statusLabel, $color])
                <div class="col-md-4">
                    <h6 class="text-{{ $color }}">{{ $statusLabel }} ({{ $project->tasks->where('status', $statusKey)->count() }})</h6>
                    <div class="kanban-col">
                        @forelse($project->tasks->where('status', $statusKey) as $task)
                        <div class="kanban-card">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $task->title }}</strong>
                                <span class="badge badge-{{ $task->priority_color }}">{{ $task->priority_label }}</span>
                            </div>
                            <div class="small text-muted">
                                {{ $task->assignee->employee_name_A ?? 'غير مُسنَد' }}
                                @if($task->due_date) - استحقاق {{ \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') }}@endif
                            </div>
                            <div class="mt-2">
                                <form action="{{ route('project_tasks.status', $task->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <select name="status" class="form-control form-control-sm d-inline-block" style="width:auto" onchange="this.form.submit()">
                                        @foreach($statuses as $sKey => [$sLabel, $sColor])
                                            <option value="{{ $sKey }}" {{ $task->status == $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <a href="{{ route('project_tasks.delete', $task->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف المهمة؟')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                        @empty
                        <div class="text-muted small text-center py-3">لا يوجد</div>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
