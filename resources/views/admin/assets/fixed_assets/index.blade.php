@extends('admin.layouts.assets')
@section('title') سجل الأصول @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('fixed_assets.index') }}">سجل الأصول</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-building ml-2"></i> سجل الأصول الثابتة</h3>
            <div class="card-tools">
                <a href="{{ route('fixed_assets.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة أصل</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="category_id" class="form-control form-control-sm">
                        <option value="">كل الفئات</option>
                        @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control form-control-sm">
                        <option value="">كل الحالات</option>
                        <option value="active" {{ request('status')=='active'?'selected':'' }}>نشط</option>
                        <option value="disposed" {{ request('status')=='disposed'?'selected':'' }}>تم التخلص منه</option>
                        <option value="fully_depreciated" {{ request('status')=='fully_depreciated'?'selected':'' }}>مستهلك بالكامل</option>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> فلترة</button></div>
            </form>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>رقم الأصل</th><th>الاسم</th><th>الفئة</th><th>تاريخ الشراء</th><th>التكلفة</th><th>مجمع الإهلاك</th><th>القيمة الدفترية</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $asset)
                    <tr>
                        <td>{{ $asset->asset_number }}</td>
                        <td>{{ $asset->name }}</td>
                        <td>{{ $asset->category->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($asset->purchase_date)->format('Y-m-d') }}</td>
                        <td>{{ number_format($asset->purchase_cost, 2) }}</td>
                        <td>{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                        <td>{{ number_format($asset->book_value, 2) }}</td>
                        <td><span class="badge badge-{{ $asset->status_color }}">{{ $asset->status_label }}</span></td>
                        <td><a href="{{ route('fixed_assets.show', $asset->id) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-4 text-muted">لا توجد أصول مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection
