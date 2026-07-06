@extends('admin.layouts.sales')
@section('title') مجموعات الأصناف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('item_categories.index') }}">مجموعات الأصناف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-layer-group ml-2"></i>
                مجموعات الأصناف
                <a href="{{ route('item_categories.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة مجموعة
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('item_categories.index') }}" class="form-inline mb-3 flex-wrap">
                <input type="text" name="name" class="form-control ml-2 mb-1"
                    placeholder="بحث بالاسم أو الكود..." value="{{ request('name') }}" style="min-width:220px">
                <select name="is_active" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="1" {{ request('is_active')==='1' ? 'selected' : '' }}>مفعّل</option>
                    <option value="0" {{ request('is_active')==='0' ? 'selected' : '' }}>معطّل</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1">
                    <i class="fas fa-search"></i> بحث
                </button>
                <a href="{{ route('item_categories.index') }}" class="btn btn-secondary mb-1">مسح</a>
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
                            <th>المجموعة الأب</th>
                            <th>الأصناف</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $cat)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $cat->code ?? '—' }}</code></td>
                            <td>
                                <strong>{{ $cat->name }}</strong>
                                @if($cat->name_en)
                                    <br><small class="text-muted">{{ $cat->name_en }}</small>
                                @endif
                            </td>
                            <td>{{ $cat->parent->name ?? '<span class="text-muted">—</span>' }}</td>
                            <td>
                                <span class="badge badge-info">{{ $cat->items_count ?? $cat->items()->count() }}</span>
                            </td>
                            <td>
                                @if($cat->is_active)
                                    <span class="badge badge-success">مفعّل</span>
                                @else
                                    <span class="badge badge-secondary">معطّل</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('item_categories.edit', $cat->id) }}"
                                   class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('item_categories.delete', $cat->id) }}"
                                   class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذه المجموعة؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد مجموعات مسجلة
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
