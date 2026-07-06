@extends('admin.layouts.inventory')
@section('title') ملخص حركة المخزون @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('inventory_reports.index') }}">التقارير</a> @endsection
@section('startpage') ملخص الحركة @endsection

@section('content')
<div class="col-12">

    <div class="card card-outline card-secondary mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('inventory_reports.movements_summary') }}" class="form-inline">
                <label class="ml-2">من</label>
                <input type="date" name="from" class="form-control ml-2" value="{{ request('from') }}">
                <label class="ml-2">إلى</label>
                <input type="date" name="to" class="form-control ml-2" value="{{ request('to') }}">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> عرض</button>
            </form>
        </div>
    </div>

    <div class="card card-info card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list-alt ml-2"></i> ملخص حركة المخزون حسب النوع</h3>
            <div class="card-tools">
                <a href="{{ route('inventory_reports.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-right ml-1"></i> رجوع</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>نوع الحركة</th>
                        <th class="text-center">عدد الحركات</th>
                        <th class="text-left">إجمالي الكمية</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $labels = [
                            'purchase_in'         => 'وارد شراء',
                            'purchase_return_out' => 'مرتجع شراء',
                            'sales_out'           => 'صادر بيع',
                            'sales_return_in'     => 'مرتجع بيع',
                            'adjustment_in'       => 'تسوية زيادة',
                            'adjustment_out'      => 'تسوية نقص',
                            'transfer_in'         => 'تحويل وارد',
                            'transfer_out'        => 'تحويل صادر',
                        ];
                    @endphp
                    @forelse($summary as $row)
                    <tr>
                        <td>{{ $labels[$row->movement_type] ?? $row->movement_type }}</td>
                        <td class="text-center">{{ number_format($row->cnt) }}</td>
                        <td class="text-left">{{ number_format($row->total_qty, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">لا توجد بيانات في هذه الفترة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
