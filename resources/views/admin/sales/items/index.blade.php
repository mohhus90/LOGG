@extends('admin.layouts.sales')
@section('title') الأصناف @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('items.index') }}">الأصناف</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-boxes ml-2"></i>
                سجل الأصناف
                <a href="{{ route('items.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة صنف
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('items.index') }}" class="form-inline mb-3 flex-wrap">
                <input type="text" name="search" class="form-control ml-2 mb-1"
                    placeholder="بحث بالاسم أو الكود..." value="{{ request('search') }}" style="min-width:220px">
                <select name="category_id" class="form-control ml-2 mb-1" style="min-width:160px">
                    <option value="">-- المجموعة --</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
                <select name="type" class="form-control ml-2 mb-1">
                    <option value="">-- النوع --</option>
                    <option value="product"        {{ request('type') == 'product'        ? 'selected' : '' }}>منتج</option>
                    <option value="service"        {{ request('type') == 'service'        ? 'selected' : '' }}>خدمة</option>
                    <option value="raw_material"   {{ request('type') == 'raw_material'   ? 'selected' : '' }}>مادة خام</option>
                    <option value="semi_finished"  {{ request('type') == 'semi_finished'  ? 'selected' : '' }}>نصف مصنّع</option>
                </select>
                <select name="is_active" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>مفعّل</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطّل</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1">
                    <i class="fas fa-search"></i> بحث
                </button>
                <a href="{{ route('items.index') }}" class="btn btn-secondary mb-1">مسح</a>
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
                            <th>المجموعة</th>
                            <th>الوحدة</th>
                            <th>النوع</th>
                            <th>سعر البيع</th>
                            <th>سعر التكلفة</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $item)
                        @php
                            $typeBadges = [
                                'product'       => ['label' => 'منتج',       'class' => 'badge-primary'],
                                'service'       => ['label' => 'خدمة',       'class' => 'badge-info'],
                                'raw_material'  => ['label' => 'مادة خام',   'class' => 'badge-warning'],
                                'semi_finished' => ['label' => 'نصف مصنّع',  'class' => 'badge-purple'],
                            ];
                            $badge = $typeBadges[$item->type] ?? ['label' => $item->type, 'class' => 'badge-secondary'];
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $item->code ?? '—' }}</code></td>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if($item->name_en)
                                    <br><small class="text-muted">{{ $item->name_en }}</small>
                                @endif
                            </td>
                            <td>{{ $item->category->name ?? '—' }}</td>
                            <td>{{ $item->unit->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </td>
                            <td class="text-success font-weight-bold">
                                {{ $item->selling_price !== null ? number_format($item->selling_price, 2) : '—' }}
                            </td>
                            <td class="text-danger">
                                {{ $item->cost_price !== null ? number_format($item->cost_price, 2) : '—' }}
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-success">مفعّل</span>
                                @else
                                    <span class="badge badge-secondary">معطّل</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('items.show', $item->id) }}"
                                   class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('items.edit', $item->id) }}"
                                   class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('items.delete', $item->id) }}"
                                   class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذا الصنف؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد أصناف مسجلة
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="9" class="text-right">
                                إجمالي الأصناف في هذه الصفحة:
                                <strong class="text-primary">{{ $data->count() }}</strong>
                                — الإجمالي الكلي:
                                <strong class="text-primary">{{ $data->total() }}</strong>
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
            {{ $data->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .badge-purple { background-color: #6f42c1; color: #fff; }
</style>
@endsection
