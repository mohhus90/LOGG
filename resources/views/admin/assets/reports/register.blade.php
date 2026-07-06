@extends('admin.layouts.assets')
@section('title') سجل الأصول التفصيلي @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_reports.register') }}">سجل الأصول التفصيلي</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-file-alt ml-2"></i> سجل الأصول التفصيلي</h3></div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الأصل</th><th>الاسم</th><th>الفئة</th><th>التكلفة</th><th>مجمع الإهلاك</th><th>القيمة الدفترية</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $asset)
                    <tr>
                        <td>{{ $asset->asset_number }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '-' }}</td>
                        <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                        <td>{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                        <td>{{ number_format($asset->book_value, 2) }}</td>
                        <td><span class="badge badge-{{ $asset->status_color }}">{{ $asset->status_label }}</span></td>
                        <td><a href="{{ route('asset_reports.schedule', $asset->id) }}" class="btn btn-xs btn-info"><i class="fas fa-calendar-alt"></i> جدول الإهلاك</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد أصول</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
