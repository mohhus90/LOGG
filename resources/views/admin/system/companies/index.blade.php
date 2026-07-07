@extends('admin.layouts.system')
@section('title') سجل الشركات @endsection
@section('start') النظام @endsection
@section('home') <a href="{{ route('companies.index') }}">سجل الشركات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-city ml-2"></i> سجل الشركات المستأجرة</h3>
        </div>
        @if(session('success'))
          <div class="alert alert-success m-3 alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
          </div>
        @endif
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>#</th><th>اسم الشركة</th><th>الهاتف</th><th>البريد الإلكتروني</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->phone ?: '-' }}</td>
                        <td>{{ $company->email ?: '-' }}</td>
                        <td>
                            @if($company->is_active)<span class="badge badge-success">مفعّلة</span>
                            @else<span class="badge badge-secondary">معطّلة</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i> تعديل</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">لا توجد شركات</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $companies->links() }}</div>
    </div>
</div>
@endsection
