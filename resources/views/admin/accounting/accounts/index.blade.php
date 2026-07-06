@extends('admin.layouts.accounting')
@section('title') دليل الحسابات @endsection
@section('start') المحاسبة @endsection
@section('home') <a href="{{ route('chart_of_accounts.index') }}">دليل الحسابات</a> @endsection
@section('startpage') عرض @endsection

@section('content')
<div class="col-12">
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sitemap ml-2"></i> دليل الحسابات</h3>
            <div class="card-tools">
                <a href="{{ route('chart_of_accounts.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> إضافة حساب
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:120px">الرقم</th>
                        <th>اسم الحساب</th>
                        <th style="width:110px">النوع</th>
                        <th style="width:90px">الطبيعة</th>
                        <th style="width:150px">الرصيد الحالي</th>
                        <th style="width:90px">الحالة</th>
                        <th style="width:110px">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tree as $row)
                        @php $account = $row['account']; @endphp
                        <tr class="{{ $account->is_group ? 'bg-light font-weight-bold' : '' }}">
                            <td>{{ $account->account_code }}</td>
                            <td style="padding-right: {{ 12 + $row['depth'] * 22 }}px">
                                @if($row['depth'] > 0)<i class="fas fa-angle-left text-muted ml-1"></i>@endif
                                {{ $account->account_name }}
                                @if($account->account_name_en)<small class="text-muted">({{ $account->account_name_en }})</small>@endif
                            </td>
                            <td><span class="badge badge-secondary">{{ $account->type_label }}</span></td>
                            <td>{{ $account->account_nature === 'debit' ? 'مدين' : 'دائن' }}</td>
                            <td>{{ number_format($account->current_balance, 2) }}</td>
                            <td>
                                @if($account->is_active)
                                    <span class="badge badge-success">مفعّل</span>
                                @else
                                    <span class="badge badge-secondary">غير مفعّل</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('chart_of_accounts.edit', $account->id) }}" class="btn btn-xs btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('chart_of_accounts.delete', $account->id) }}" class="btn btn-xs btn-danger"
                                   onclick="return confirm('حذف هذا الحساب؟')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">لا توجد حسابات مسجلة</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
