@extends('admin.layouts.assets')
@section('title') سجل تشغيل الإهلاك @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_depreciation.history') }}">سجل تشغيل الإهلاك</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history ml-2"></i> سجل تشغيل الإهلاك</h3>
            <div class="card-tools">
                <a href="{{ route('asset_depreciation.form') }}" class="btn btn-primary btn-sm"><i class="fas fa-play"></i> تشغيل إهلاك جديد</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>الأصل</th><th>الشهر/السنة</th><th>قيمة الإهلاك</th><th>تاريخ التشغيل</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $entry)
                    <tr>
                        <td>{{ $entry->fixedAsset->name ?? '-' }} ({{ $entry->fixedAsset->asset_number ?? '' }})</td>
                        <td>{{ $entry->period_month }}/{{ $entry->period_year }}</td>
                        <td>{{ number_format($entry->depreciation_amount, 2) }}</td>
                        <td>{{ $entry->run_at ? \Carbon\Carbon::parse($entry->run_at)->format('Y-m-d H:i') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">لا يوجد سجل إهلاك بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
