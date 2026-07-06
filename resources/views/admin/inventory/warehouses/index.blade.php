@extends('admin.layouts.inventory')
@section('title') المخازن @endsection
@section('start') المخازن @endsection
@section('home') <a href="{{ route('warehouses.index') }}">المخازن</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-warehouse ml-2"></i>
                سجل المخازن
                <a href="{{ route('warehouses.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة مخزن
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('warehouses.index') }}" class="form-inline mb-3 flex-wrap">
                <input type="text" name="search" class="form-control ml-2 mb-1"
                    placeholder="بحث بالاسم..." value="{{ request('search') }}" style="min-width:240px">
                <select name="is_active" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>مفعّل</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطّل</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1"><i class="fas fa-search"></i> بحث</button>
                <a href="{{ route('warehouses.index') }}" class="btn btn-secondary mb-1">مسح</a>
            </form>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>الكود</th>
                            <th>الاسم</th>
                            <th>الفرع</th>
                            <th>الموقع</th>
                            <th>افتراضي</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $warehouse)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $warehouse->code ?? '—' }}</code></td>
                            <td><strong>{{ $warehouse->name }}</strong></td>
                            <td>{{ $warehouse->branch->name ?? '—' }}</td>
                            <td>{{ $warehouse->location ?? '—' }}</td>
                            <td>
                                @if($warehouse->is_default)
                                    <span class="badge badge-primary"><i class="fas fa-star"></i></span>
                                @endif
                            </td>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="badge badge-success">مفعّل</span>
                                @else
                                    <span class="badge badge-secondary">معطّل</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('warehouses.delete', $warehouse->id) }}" class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذا المخزن؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-warehouse fa-2x mb-2 d-block"></i>
                                لا توجد مخازن مسجلة
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
