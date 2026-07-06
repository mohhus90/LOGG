@extends('admin.layouts.treasury')
@section('title') الشيكات @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('cheques.index') }}">الشيكات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-money-check-alt ml-2"></i> الشيكات</h3></div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="direction" class="form-control form-control-sm">
                        <option value="">كل الاتجاهات</option>
                        <option value="received" {{ request('direction')=='received'?'selected':'' }}>واردة (من عملاء)</option>
                        <option value="issued" {{ request('direction')=='issued'?'selected':'' }}>صادرة (لموردين)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">كل الحالات</option>
                        <option value="under_collection" {{ request('status')=='under_collection'?'selected':'' }}>تحت التحصيل</option>
                        <option value="collected" {{ request('status')=='collected'?'selected':'' }}>تم التحصيل</option>
                        <option value="bounced" {{ request('status')=='bounced'?'selected':'' }}>مرتجع</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> فلترة</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الشيك</th><th>الاتجاه</th><th>الطرف</th><th>البنك</th><th>تاريخ الاستحقاق</th><th>المبلغ</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $cheque)
                    <tr>
                        <td>{{ $cheque->cheque_number }}</td>
                        <td>{{ $cheque->direction === 'received' ? 'واردة' : 'صادرة' }}</td>
                        <td>{{ $cheque->party_name }}</td>
                        <td>{{ $cheque->bank_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($cheque->due_date)->format('Y-m-d') }}</td>
                        <td>{{ number_format($cheque->amount, 2) }}</td>
                        <td><span class="badge badge-{{ $cheque->status_color }}">{{ $cheque->status_label }}</span></td>
                        <td><a href="{{ route('cheques.show', $cheque->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد شيكات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
