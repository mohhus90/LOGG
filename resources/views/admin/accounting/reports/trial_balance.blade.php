@extends('admin.layouts.accounting')
@section('title') ميزان المراجعة @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('accounting_reports.trial_balance') }}">ميزان المراجعة</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-balance-scale ml-2"></i> ميزان المراجعة</h3></div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
                <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> عرض</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الحساب</th><th>اسم الحساب</th><th>مدين</th><th>دائن</th><th>الرصيد</th></tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr class="{{ $row['account']->is_group ? 'bg-light font-weight-bold' : '' }}">
                        <td>{{ $row['account']->account_code }}</td>
                        <td>{{ $row['account']->account_name }}</td>
                        <td>{{ number_format($row['debit'], 2) }}</td>
                        <td>{{ number_format($row['credit'], 2) }}</td>
                        <td>{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">لا توجد حركة خلال هذه الفترة</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-secondary text-white font-weight-bold">
                        <td colspan="2">الإجمالي</td>
                        <td>{{ number_format($totalDebit, 2) }}</td>
                        <td>{{ number_format($totalCredit, 2) }}</td>
                        <td>{{ number_format($totalDebit - $totalCredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
