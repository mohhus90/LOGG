@extends('admin.layouts.treasury')
@section('title') الحسابات البنكية @endsection
@section('start') الخزينة @endsection
@section('home') <a href="{{ route('bank_accounts.index') }}">الحسابات البنكية</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-university ml-2"></i> الحسابات البنكية</h3>
            <div class="card-tools">
                <a href="{{ route('bank_accounts.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> إضافة حساب بنكي</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr><th>البنك</th><th>اسم الحساب</th><th>رقم الحساب</th><th>الفرع</th><th>الحساب المحاسبي</th><th>الرصيد الحالي</th><th>الحالة</th><th>إجراء</th></tr>
                </thead>
                <tbody>
                    @forelse($data as $bank)
                    <tr>
                        <td>{{ $bank->bank_name }}</td>
                        <td>{{ $bank->account_name }}</td>
                        <td>{{ $bank->account_number ?? '-' }}</td>
                        <td>{{ $bank->branch->name ?? '-' }}</td>
                        <td>{{ $bank->glAccount->account_name ?? '-' }}</td>
                        <td>{{ number_format($bank->current_balance, 2) }}</td>
                        <td>
                            @if($bank->is_active)<span class="badge badge-success">مفعّل</span>
                            @else<span class="badge badge-secondary">غير مفعّل</span>@endif
                        </td>
                        <td>
                            <a href="{{ route('bank_accounts.edit', $bank->id) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('bank_accounts.delete', $bank->id) }}" class="btn btn-xs btn-danger" onclick="return confirm('حذف الحساب البنكي؟')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-4 text-muted">لا توجد حسابات بنكية مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
