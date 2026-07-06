@extends('admin.layouts.purchasing')
@section('title') الموردون @endsection
@section('start') المشتريات @endsection
@section('home') <a href="{{ route('suppliers.index') }}">الموردون</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-truck ml-2"></i>
                سجل الموردين
                <a href="{{ route('suppliers.create') }}" class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-plus"></i> إضافة مورد
                </a>
            </h3>
        </div>

        <div class="card-body pb-0">
            <form method="GET" action="{{ route('suppliers.index') }}" class="form-inline mb-3 flex-wrap">
                <input type="text" name="search" class="form-control ml-2 mb-1"
                    placeholder="بحث بالاسم أو الكود أو الهاتف..." value="{{ request('search') }}" style="min-width:240px">
                <select name="type" class="form-control ml-2 mb-1">
                    <option value="">-- النوع --</option>
                    <option value="company" {{ request('type') == 'company' ? 'selected' : '' }}>شركة</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>فرد</option>
                </select>
                <select name="is_active" class="form-control ml-2 mb-1">
                    <option value="">-- الحالة --</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>مفعّل</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>معطّل</option>
                </select>
                <button type="submit" class="btn btn-primary ml-2 mb-1">
                    <i class="fas fa-search"></i> بحث
                </button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary mb-1">مسح</a>
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
                            <th>النوع</th>
                            <th>الهاتف</th>
                            <th>إجمالي المستحقات</th>
                            <th>الحالة</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $supplier)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><code>{{ $supplier->code ?? '—' }}</code></td>
                            <td>
                                <strong>{{ $supplier->name }}</strong>
                                @if($supplier->name_en)
                                    <br><small class="text-muted">{{ $supplier->name_en }}</small>
                                @endif
                            </td>
                            <td>
                                @if($supplier->type == 'company')
                                    <span class="badge badge-primary"><i class="fas fa-building ml-1"></i> شركة</span>
                                @else
                                    <span class="badge badge-secondary"><i class="fas fa-user ml-1"></i> فرد</span>
                                @endif
                            </td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td class="{{ ($supplier->total_debt ?? 0) > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">
                                {{ number_format($supplier->total_debt ?? 0, 2) }}
                            </td>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge badge-success">مفعّل</span>
                                @else
                                    <span class="badge badge-secondary">معطّل</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('suppliers.show', $supplier->id) }}"
                                   class="btn btn-xs btn-info" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('suppliers.edit', $supplier->id) }}"
                                   class="btn btn-xs btn-warning" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('suppliers.delete', $supplier->id) }}"
                                   class="btn btn-xs btn-danger" title="حذف"
                                   onclick="return confirm('هل تريد حذف هذا المورد؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-truck fa-2x mb-2 d-block"></i>
                                لا يوجد موردون مسجلون
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($data->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-right">
                                إجمالي الموردين الكلي:
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
