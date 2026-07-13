@extends('admin.layouts.admin')

@section('title')زيادات الرواتب@endsection
@section('start')إدارة الموارد البشرية@endsection
@section('home')<a href="{{ route('salary_increases.index') }}">زيادات الرواتب</a>@endsection
@section('startpage')عرض@endsection

@section('content')
<div class="col-12">

  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('error') }}
    </div>
  @endif

  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title"><i class="fas fa-arrow-trend-up mr-2"></i>سجل زيادات الرواتب</h3>
      <a href="{{ route('salary_increases.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i>تطبيق زيادة جديدة
      </a>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-bordered table-sm">
        <thead class="thead-light">
          <tr>
            <th>النطاق</th>
            <th>الطريقة</th>
            <th>القيمة</th>
            <th>تاريخ السريان</th>
            <th>عدد الموظفين المتأثرين</th>
            <th>ملاحظات</th>
            <th>بواسطة</th>
            <th>تاريخ الإضافة</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rules as $rule)
            <tr>
              <td>{{ $rule->scopeLabel() }}</td>
              <td>{{ $rule->method === 'percentage' ? 'نسبة مئوية' : 'مبلغ ثابت' }}</td>
              <td>{{ $rule->method === 'percentage' ? number_format($rule->value, 2) . '%' : number_format($rule->value, 2) }}</td>
              <td>{{ $rule->effective_date }}</td>
              <td><span class="badge badge-info">{{ $rule->matched_count }}</span></td>
              <td>{{ $rule->notes ?: '—' }}</td>
              <td>{{ optional($rule->addedBy)->name ?? '—' }}</td>
              <td>{{ $rule->created_at }}</td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">لا يوجد زيادات مطبّقة بعد</td></tr>
          @endforelse
        </tbody>
      </table>
      {{ $rules->links() }}
    </div>
  </div>

</div>
@endsection
