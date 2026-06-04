@extends('admin.layouts.admin')
@section('title') الهيكل الوظيفي @endsection
@section('start') الإعدادات @endsection
@section('home')
    <a href="{{ route('org_levels.index') }}">الهيكل الوظيفي</a>
@endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">الهيكل الوظيفي للشركة</h3>
            <div>
                <a href="{{ route('org_levels.templates') }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-layer-group"></i> نماذج جاهزة
                </a>
                <a href="{{ route('org_levels.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus"></i> إضافة مستوى
                </a>
            </div>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($levels->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-sitemap fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">لم يتم إنشاء هيكل وظيفي بعد</h4>
                    <p class="text-muted">يمكنك البدء بتحميل نموذج جاهز أو إنشاء هيكل مخصص</p>
                    <a href="{{ route('org_levels.templates') }}" class="btn btn-warning me-2">
                        <i class="fas fa-layer-group"></i> اختر نموذجاً جاهزاً
                    </a>
                    <a href="{{ route('org_levels.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> إنشاء هيكل مخصص
                    </a>
                </div>
            @else
                {{-- عرض شجري --}}
                <div class="org-tree mb-4">
                    @include('admin.org_levels._tree_node', ['nodes' => $tree, 'depth' => 0])
                </div>

                {{-- جدول تفصيلي --}}
                <hr>
                <h5 class="mb-3">جدول تفصيلي بجميع المستويات</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>المستوى الوظيفي</th>
                                <th>الاسم الإنجليزي</th>
                                <th>الترتيب</th>
                                <th>يتبع</th>
                                <th>النوع</th>
                                <th>عمولة بائع</th>
                                <th>عمولة مدير</th>
                                <th>الوظائف المرتبطة</th>
                                <th>إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($levels->sortBy('level_order') as $level)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $level->name }}</strong></td>
                                <td>{{ $level->name_en ?? '-' }}</td>
                                <td><span class="badge bg-secondary">{{ $level->level_order }}</span></td>
                                <td>{{ $level->parent?->name ?? '<span class="text-muted">—</span>' }}</td>
                                <td>{!! $level->level_type_badge !!}</td>
                                <td>
                                    @if($level->receives_seller_commission)
                                        <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-light text-dark"><i class="fas fa-times"></i></span>
                                    @endif
                                </td>
                                <td>
                                    @if($level->receives_manager_commission)
                                        <span class="badge bg-primary"><i class="fas fa-check"></i></span>
                                    @else
                                        <span class="badge bg-light text-dark"><i class="fas fa-times"></i></span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $level->jobs()->count() }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('org_levels.edit', $level->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('org_levels.delete', $level->id) }}"
                                       class="btn btn-sm btn-danger are_you_sure"
                                       onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('style')
<style>
.org-node {
    border-right: 3px solid #dee2e6;
    margin-right: 20px;
    padding: 8px 15px;
    position: relative;
}
.org-node::before {
    content: '';
    position: absolute;
    right: -3px;
    top: 50%;
    width: 15px;
    height: 2px;
    background: #dee2e6;
}
.org-node-card {
    display: inline-flex;
    align-items: center;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 15px;
    gap: 10px;
    transition: box-shadow 0.2s;
}
.org-node-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.org-tree > .org-node { border-right: none; }
.org-tree > .org-node::before { display: none; }
</style>
@endsection
