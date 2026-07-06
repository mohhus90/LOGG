@extends('admin.layouts.crm')
@section('title') {{ $lead->name }} @endsection
@section('start') إدارة علاقات العملاء @endsection
@section('home') <a href="{{ route('crm_leads.index') }}">العملاء المحتملون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-lg-8">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus ml-2"></i> {{ $lead->name }}
                <span class="badge badge-{{ $lead->status_color }} mr-2">{{ $lead->status_label }}</span>
            </h3>
            <div class="card-tools">
                @if($lead->status !== 'converted')
                <form action="{{ route('crm_leads.convert', $lead->id) }}" method="POST" class="d-inline" onsubmit="return confirm('تحويل هذا العميل المحتمل إلى عميل فعلي؟')">
                    @csrf
                    <button class="btn btn-sm btn-success"><i class="fas fa-user-check ml-1"></i> تحويل إلى عميل</button>
                </form>
                @else
                <a href="{{ route('sales_customers.show', $lead->converted_customer_id) }}" class="btn btn-sm btn-outline-success">عرض العميل</a>
                @endif
                <a href="{{ route('crm_opportunities.create', ['lead_id' => $lead->id]) }}" class="btn btn-sm btn-primary"><i class="fas fa-bullseye ml-1"></i> فرصة بيعية جديدة</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>الهاتف:</strong> {{ $lead->phone ?? '-' }}</div>
                <div class="col-md-4"><strong>البريد الإلكتروني:</strong> {{ $lead->email ?? '-' }}</div>
                <div class="col-md-4"><strong>المصدر:</strong> {{ $lead->source ?? '-' }}</div>
            </div>
            @if($lead->notes)<p class="mt-2"><strong>ملاحظات:</strong> {{ $lead->notes }}</p>@endif

            @if($lead->opportunities->count())
            <hr>
            <h6>الفرص البيعية المرتبطة</h6>
            <ul class="list-group mb-3">
                @foreach($lead->opportunities as $opp)
                <li class="list-group-item d-flex justify-content-between">
                    <a href="{{ route('crm_opportunities.show', $opp->id) }}">{{ $opp->title }}</a>
                    <span class="badge badge-{{ $opp->stage_color }}">{{ $opp->stage_label }}</span>
                </li>
                @endforeach
            </ul>
            @endif

            <hr>
            <h6>المتابعات</h6>
            <form action="{{ route('crm_activities.store') }}" method="POST" class="mb-3">
                @csrf
                <input type="hidden" name="linked_type" value="lead">
                <input type="hidden" name="linked_id" value="{{ $lead->id }}">
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
