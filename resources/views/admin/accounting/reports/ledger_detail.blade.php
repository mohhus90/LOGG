@extends('admin.layouts.accounting')
@section('title') كشف حساب @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('accounting_reports.ledger') }}">كشف حساب</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list-alt ml-2"></i> كشف حساب تفصيلي</h3></div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-4">
                    <select name="account_id" class="form-control form-control-sm select2" onchange="this.form.submit()">
                        <option value="">-- اختر الحساب --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ ($account && $account->id == $acc->id) ? 'selected' : '' }}>
                                {{ $acc->account_code }} - {{ $acc->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
                <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> عرض</button></div>
            </form>
        </div>
        @if($account)
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>التاريخ</th><th>رقم القيد</th><th>البيان</th><th>الطرف</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr>
                </thead>
                <tbody>
                    @forelse($lines as $line)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($line->journalEntry->entry_date)->format('Y-m-d') }}</td>
                        <td><a href="{{ route('journal_entries.show', $line->journal_entry_id) }}">{{ $line->journalEntry->entry_number }}</a></td>
                        <td>{{ $line->description }}</td>
                        <td>{{ $line->party_name ?? '-' }}</td>
                        <td>{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                        <td>{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                        <td>{{ number_format($line->running_balance, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد حركة على هذا الحساب خلال الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @else
        <div class="card-body text-center text-muted py-5">اختر حسابًا لعرض كشف الحساب</div>
        @endif
    </div>
</div>
@endsection
