@extends('admin.layouts.assets')
@section('title') فئات الأصول @endsection
@section('start') الأصول الثابتة @endsection
@section('home') <a href="{{ route('asset_categories.index') }}">فئات الأصول</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-tags ml-2"></i> فئات الأصول</h3>
            <div class="card-tools">
                <a href="{{ route('asset_categories.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة فئة</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>الاسم</th><th>العمر الافتراضي (سنوات)</th><th>حساب الأصل</th><th>حساب مجمع الإهلاك</th><th>حساب مصروف الإهلاك</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $cat)
                    <tr>
                        <td>{{ $cat->name }}</td>
                        <td>{{ $cat->default_useful_life_years }}</td>
                        <td>{{ $cat->assetGlAccount->account_name ?? '-' }}</td>
                        <td>{{ $cat->accumDepreciationGlAccount->account_name ?? '-' }}</td>
                        <td>{{ $cat->depreciationExpenseGlAccount->account_name ?? '-' }}</td>
                        <td>
                            @if($cat->is_active)<span class="badge badge-success">مفعّل</span>
                            @else<span class="badge badge-secondary">غير مفعّل</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('asset_categories.edit', $cat->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('asset_categories.delete', $cat->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف الفئة؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد فئات أصول</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
