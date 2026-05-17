{{-- FILE: resources/views/admin/kpi/definitions.blade.php --}}
@extends('admin.layouts.admin')
@section('title') مؤشرات الأداء KPIs @endsection
@section('start') الأداء @endsection
@section('home') <a href="{{ route('kpi.definitions') }}">مؤشرات الأداء</a> @endsection
@section('startpage') تعريف المؤشرات @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-chart-line ml-2 text-primary"></i>مؤشرات الأداء (KPIs)
      <a href="{{ route('kpi.create_definition') }}" class="btn btn-sm btn-success mr-2">
        <i class="fas fa-plus"></i> إضافة مؤشر
      </a>
      <a href="{{ route('kpi.scores') }}" class="btn btn-sm btn-info mr-1">
        <i class="fas fa-edit"></i> إدخال قراءات الشهر
      </a>
      <a href="{{ route('kpi.report') }}" class="btn btn-sm btn-primary mr-1">
        <i class="fas fa-chart-bar"></i> تقرير الأداء
      </a>
    </h3>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-2">{{ session('success') }}</div>
  @endif

  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-3">
        <div class="small-box bg-primary">
          <div class="inner"><h3>{{ $kpis->count() }}</h3><p>إجمالي المؤشرات</p></div>
          <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-success">
          <div class="inner"><h3>{{ $kpis->where('is_active',1)->count() }}</h3><p>مؤشرات نشطة</p></div>
          <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-warning">
          <div class="inner"><h3>{{ $kpis->where('affects_salary',1)->count() }}</h3><p>تؤثر على الراتب</p></div>
          <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="small-box bg-info">
          <div class="inner"><h3>{{ round($kpis->sum('weight')) }}%</h3><p>مجموع الأوزان</p></div>
          <div class="icon"><i class="fas fa-balance-scale"></i></div>
        </div>
      </div>
    </div>

    <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>اسم المؤشر</th>
          <th>الكود</th>
          <th>الفئة</th>
          <th>وحدة القياس</th>
          <th>الهدف</th>
          <th>الوزن %</th>
          <th>تأثير الراتب</th>
          <th>أقصى مكافأة</th>
          <th>أقصى خصم</th>
          <th>الحالة</th>
          <th>إجراء</th>
        </tr>
      </thead>
      <tbody>
        @forelse($kpis as $kpi)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $kpi->name }}</strong><br><small class="text-muted">{{ $kpi->description }}</small></td>
          <td><code>{{ $kpi->code }}</code></td>
          <td><span class="badge badge-secondary">{{ $kpi->category_label }}</span></td>
          <td>{{ $kpi->measurement_unit ?? '—' }}</td>
          <td>{{ number_format($kpi->target_value, 1) }}</td>
          <td>
            <div class="progress" style="height:18px">
              <div class="progress-bar" style="width:{{ $kpi->weight }}%">{{ $kpi->weight }}%</div>
            </div>
          </td>
          <td>{!! $kpi->effect_type_label !!}</td>
          <td class="text-success">{{ $kpi->max_bonus_pct > 0 ? $kpi->max_bonus_pct.'%' : '—' }}</td>
          <td class="text-danger">{{ $kpi->max_deduction_pct > 0 ? $kpi->max_deduction_pct.'%' : '—' }}</td>
          <td>
            @if($kpi->is_active)
              <span class="badge badge-success">نشط</span>
            @else
              <span class="badge badge-secondary">معطل</span>
            @endif
          </td>
          <td>
            <a href="{{ route('kpi.edit_definition', $kpi->id) }}" class="btn btn-xs btn-warning">
              <i class="fas fa-edit"></i>
            </a>
            <a href="{{ route('kpi.delete_definition', $kpi->id) }}" class="btn btn-xs btn-danger"
               onclick="return confirm('حذف هذا المؤشر؟')">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="12" class="text-center py-4">
            لا توجد مؤشرات أداء بعد.
            <a href="{{ route('kpi.create_definition') }}">أضف الآن</a>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
    </div>
  </div>
</div>
</div>
@endsection
