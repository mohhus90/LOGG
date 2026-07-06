@extends('admin.layouts.crm')
@section('title') العملاء المحتملون @endsection
@section('start') إدارة علاقات العملاء @endsection
@section('home') <a href="{{ route('crm_leads.index') }}">العملاء المحتملون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus ml-2"></i> العملاء المحتملون</h3>
            <div class="card-tools">
                <a href="{{ route('crm_leads.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> عميل محتمل جديد</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">كل الحالات</option>
                        <option value="new" {{ request('status')=='new'?'selected':'' }}>جديد</option>
                        <option value="contacted" {{ request('status')=='contacted'?'selected':'' }}>تم التواصل</option>
                        <option value="qualified" {{ request('status')=='qualified'?'selected':'' }}>مؤهّل</option>
                        <option value="converted" {{ request('status')=='converted'?'selected':'' }}>تم التحويل</option>
                        <option value="lost" {{ request('status')=='lost'?'selected':'' }}>خسارة</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> فلترة</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>الاسم</th><th>الهاتف</th><th>المصدر</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $lead)
                    <tr>
                        <td>{{ $lead->name }}</td>
                        <td>{{ $lead->phone ?? '-' }}</td>
                        <td>{{ $lead->source ?? '-' }}</td>
                        <td><span class="badge badge-{{ $lead->status_color }}">{{ $lead->status_label }}</span></td>
                        <td>
                            <a href="{{ route('crm_leads.show', $lead->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('crm_leads.edit', $lead->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('crm_leads.delete', $lead->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف العميل المحتمل؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">لا يوجد عملاء محتملون</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
