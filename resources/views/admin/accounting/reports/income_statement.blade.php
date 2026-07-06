@extends('admin.layouts.accounting')
@section('title') قائمة الدخل @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('accounting_reports.income_statement') }}">قائمة الدخل</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-invoice-dollar ml-2"></i> قائمة الدخل</h3></div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3"><input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm"></div>
                <div class="col-md-3"><input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> عرض</button></div>
            </form>
        </div>
        <div class="card-body">
            <h5 class="text-success">الإيرادات</h5>
            <table class="table table-bordered table-sm mb-4">
                @forelse($revenueRows as $row)
                <tr><td>{{ $row['account']->account_name }}</td><td class="text-left" style="width:180px">{{ number_format($row['amount'], 2) }}</td></tr>
                @empty
                <tr><td class="text-center text-muted py-3">لا توجد إيرادات</td></tr>
                @endforelse
                <tr class="font-weight-bold bg-light"><td>إجمالي الإيرادات</td><td class="text-left">{{ number_format($totalRevenue, 2) }}</td></tr>
            </table>

            <h5 class="text-danger">المصروفات</h5>
            <table class="table table-bordered table-sm mb-4">
                @forelse($expenseRows as $row)
                <tr><td>{{ $row['account']->account_name }}</td><td class="text-left" style="width:180px">{{ number_format($row['amount'], 2) }}</td></tr>
                @empty
                <tr><td class="text-center text-muted py-3">لا توجد مصروفات</td></tr>
                @endforelse
                <tr class="font-weight-bold bg-light"><td>إجمالي المصروفات</td><td class="text-left">{{ number_format($totalExpense, 2) }}</td></tr>
            </table>

            <table class="table table-bordered">
                <tr class="{{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white font-weight-bold">
                    <td>{{ $netProfit >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}</td>
                    <td class="text-left" style="width:180px">{{ number_format(abs($netProfit), 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endsection
