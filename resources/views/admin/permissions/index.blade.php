@extends('admin.layouts.admin')
@section('title') صلاحيات المستخدمين @endsection
@section('start') الإدارة @endsection
@section('home') <a href="{{ route('admin.permissions.index') }}">صلاحيات المستخدمين</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title card_title_center">
                <i class="fas fa-user-shield ml-2"></i>
                إدارة صلاحيات المستخدمين
            </h3>
        </div>

        @if(session('success'))
            <div class="alert alert-success mx-3 mt-2">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger mx-3 mt-2">{{ session('error') }}</div>
        @endif

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>النوع</th>
                            <th>إجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                @if($admin->is_super_admin)
                                    <span class="badge badge-danger">سوبر أدمن</span>
                                @else
                                    <span class="badge badge-secondary">أدمن عادي</span>
                                @endif
                            </td>
                            <td>
                                @if(!$admin->is_super_admin)
                                <a href="{{ route('admin.permissions.edit', $admin->id) }}"
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> تعديل الصلاحيات
                                </a>
                                @else
                                    <span class="text-muted">يملك كل الصلاحيات</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center">لا يوجد مستخدمون آخرون</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection