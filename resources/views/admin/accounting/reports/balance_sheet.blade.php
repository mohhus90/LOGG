@extends('admin.layouts.accounting')
@section('title') الميزانية العمومية @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('accounting_reports.balance_sheet') }}">الميزانية العمومية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-university ml-2"></i> الميزانية العمومية</h3></div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3"><input type="date" name="as_of" value="{{ $asOf }}" class="form-control form-control-sm"></div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> عرض</button></div>
            </form>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>الأصول</h5>
                    <table class="table table-bordered table-sm">
                        @forelse($assetRows as $row)
                        <tr><td>{{ $row['account']->account_name }}</td><td class="text-left" style="width:150px">{{ number_format($row['amount'], 2) }}</td></tr>
                        @empty
                        <tr><td class="text-center text-muted py-3">لا يوجد</td></tr>
                        @endforelse
                        <tr class="font-weight-bold bg-light"><td>إجمالي الأصول</td><td class="text-left">{{ number_format($totalAssets, 2) }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>الالتزامات</h5>
                    <table class="table table-bordered table-sm">
                        @forelse($liabilityRows as $row)
                        <tr><td>{{ $row['account']->account_name }}</td><td class="text-left" style="width:150px">{{ number_format($row['amount'], 2) }}</td></tr>
                        @empty
                        <tr><td class="text-center text-muted py-3">لا يوجد</td></tr>
                        @endforelse
                        <tr class="font-weight-bold bg-light"><td>إجمالي الالتزامات</td><td class="text-left">{{ number_format($totalLiabilities, 2) }}</td></tr>
                    </table>

                    <h5>حقوق الملكية</h5>
                    <table class="table table-bordered table-sm">
                        @forelse($equityRows as $row)
                        <tr><td>{{ $row['account']->account_name }}</td><td class="text-left" style="width:150px">{{ number_format($row['amount'], 2) }}</td></tr>
                        @empty
                        <tr><td class="text-center text-muted py-3">لا يوجد</td></tr>
                        @endforelse
                        <tr><td>ربح/خسارة العام حتى تاريخه</td><td class="text-left">{{ number_format($currentPeriodProfit, 2) }}</td></tr>
                        <tr class="font-weight-bold bg-light"><td>إجمالي حقوق الملكية</td><td class="text-left">{{ number_format($totalEquity, 2) }}</td></tr>
                    </table>
                    <table class="table table-bordered">
                        <tr class="font-weight-bold {{ abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01 ? 'bg-success' : 'bg-danger' }} text-white">
                            <td>إجمالي الالتزامات + حقوق الملكية</td>
                            <td class="text-left" style="width:150px">{{ number_format($totalLiabilities + $totalEquity, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
