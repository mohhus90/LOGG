@extends('admin.layouts.projects')
@section('title') تعديل مشروع @endsection
@section('start') إدارة المشاريع @endsection
@section('home') <a href="{{ route('projects.index') }}">المشاريع</a> @endsection
@section('startpage') تعديل @endsection

@section('content')
<div class="col-lg-7">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-edit ml-2"></i> تعديل: {{ $project->name }}</h3></div>
        <form action="{{ route('projects.update', $project->id) }}" method="POST">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="form-group">
                    <label>اسم المشروع <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $project->name) }}" required>
                </div>
                <div class="form-group">
                    <label>العميل (اختياري)</label>
                    <select name="customer_id" class="form-control select2">
                        <option value="">-- بدون --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $project->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group"><label>تاريخ البدء</label><input type="date" name="start_date" class="form-control" value="{{ optional($project->start_date)->format('Y-m-d') }}"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>تاريخ الانتهاء المخطط</label><input type="date" name="end_date" class="form-control" value="{{ optional($project->end_date)->format('Y-m-d') }}"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group"><label>الميزانية</label><input type="number" step="0.01" name="budget" class="form-control" value="{{ $project->budget }}"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label>الحالة <span class="text-danger">*</span></label>
                    <select name="status" class="form-control" required>
                        @foreach(\App\Models\Project::statusOptions() as $key => [$label, $color])
                            <option value="{{ $key }}" {{ $project->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>ملاحظات</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $project->notes) }}</textarea>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save ml-1"></i> حفظ</button>
                <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection
