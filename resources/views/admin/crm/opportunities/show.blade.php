@extends('admin.layouts.crm')
@section('title') {{ $opportunity->title }} @endsection
@section('start') إدارة علاقات العملاء @endsection
@section('home') <a href="{{ route('crm_opportunities.index') }}">الفرص البيعية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bullseye ml-2"></i> {{ $opportunity->title }}
                <span class="badge badge-{{ $opportunity->stage_color }} mr-2">{{ $opportunity->stage_label }}</span>
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>الطرف:</strong> {{ $opportunity->lead->name ?? $opportunity->customer->name ?? '-' }}</div>
                <div class="col-md-4"><strong>القيمة:</strong> {{ number_format($opportunity->value, 2) }}</div>
                <div class="col-md-4"><strong>تاريخ الإغلاق المتوقع:</strong> {{ $opportunity->expected_close_date ? \Carbon\Carbon::parse($opportunity->expected_close_date)->format('Y-m-d') : '-' }}</div>
            </div>
            @if($opportunity->notes)<p class="mt-2"><strong>ملاحظات:</strong> {{ $opportunity->notes }}</p>@endif

            <hr>
            <h6>تحديث المرحلة</h6>
            <form action="{{ route('crm_opportunities.stage', $opportunity->id) }}" method="POST" class="form-inline mb-3">
                @csrf
                <select name="stage" class="form-control ml-2">
                    @foreach(\App\Models\CrmOpportunity::stageOptions() as $key => [$label, $color])
                        <option value="{{ $key }}" {{ $opportunity->stage == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary"><i class="fas fa-save ml-1"></i> تحديث</button>
            </form>

            <hr>
            <h6>المتابعات</h6>
            <form action="{{ route('crm_activities.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="hidden" name="linked_type" value="opportunity">
                <input type="hidden" name="linked_id" value="{{ $opportunity->id }}">
                <div class="row">
                    <div class="col-md-2">
                        <select name="type" class="form-control form-control-sm">
                            <option value="call">مكالمة</option>
                            <option value="meeting">اجتماع</option>
                            <option value="note">ملاحظة</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="activity_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="تفاصيل المتابعة" required>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-sm btn-primary btn-block"><i class="fas fa-plus"></i> إضافة</button>
                    </div>
                </div>
            </form>

            <table class="table table-bordered table-sm">
                <thead class="thead-dark"><tr><th>التاريخ</th><th>النوع</th><th>التفاصيل</th><th></th></tr></thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($activity->activity_date)->format('Y-m-d') }}</td>
                        <td>{{ $activity->type_label }}</td>
                        <td>{{ $activity->notes }}</td>
                        <td><a href="{{ route('crm_activities.delete', $activity->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف المتابعة؟')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">لا توجد متابعات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
