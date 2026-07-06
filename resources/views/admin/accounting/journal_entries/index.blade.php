@extends('admin.layouts.accounting')
@section('title') القيود اليومية @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('journal_entries.index') }}">القيود اليومية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-book ml-2"></i> القيود اليومية</h3>
            <div class="card-tools">
                <a href="{{ route('journal_entries.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> قيد يدوي جديد</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row mb-3">
                <div class="col-md-2"><input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm" placeholder="من تاريخ"></div>
                <div class="col-md-2"><input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm" placeholder="إلى تاريخ"></div>
                <div class="col-md-2">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">كل الحالات</option>
                        <option value="posted"   {{ request('status')=='posted'?'selected':'' }}>مرحّل</option>
                        <option value="reversed" {{ request('status')=='reversed'?'selected':'' }}>معكوس</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> فلترة</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم القيد</th><th>التاريخ</th><th>المصدر</th><th>البيان</th><th>مدين</th><th>دائن</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $entry)
                    <tr>
                        <td>{{ $entry->entry_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}</td>
                        <td>{{ $entry->source_module ?? 'يدوي' }}</td>
                        <td>{{ $entry->description }}</td>
                        <td>{{ number_format($entry->total_debit, 2) }}</td>
                        <td>{{ number_format($entry->total_credit, 2) }}</td>
                        <td><span class="badge badge-{{ $entry->status_color }}">{{ $entry->status_label }}</span></td>
                        <td><a href="{{ route('journal_entries.show', $entry->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد قيود</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
