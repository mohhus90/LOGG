@extends('admin.layouts.sales')
@section('title') وحدات القياس @endsection
@section('start') المبيعات @endsection
@section('home') <a href="{{ route('item_units.index') }}">وحدات القياس</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-ruler-combined ml-2"></i> وحدات القياس</h3>
            <div class="card-tools">
                <a href="{{ route('item_units.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> إضافة وحدة
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if(session('success'))
                <div class="alert alert-success mx-3 mt-3">{{ session('success') }}</div>
            @endif
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>الاسم بالعربية</th>
                        <th>الاسم بالإنجليزية</th>
                        <th>الرمز</th>
                        <th>الحالة</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $unit)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $unit->name }}</strong></td>
                        <td>{{ $unit->name_en }}</td>
                        <td><span class="badge badge-info">{{ $unit->symbol }}</span></td>
                        <td>
                            @if($unit->is_active)
                                <span class="badge badge-success">مفعّل</span>
                            @else
                                <span class="badge badge-secondary">غير مفعّل</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('item_units.edit', $unit->id) }}" class="btn btn-xs btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('item_units.delete', $unit->id) }}" class="btn btn-xs btn-danger"
                               onclick="return confirm('حذف هذه الوحدة؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد وحدات قياس مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $data->links() }}</div>
    </div>
</div>
@endsection
