{{-- FILE: resources/views/admin/branch_commissions/index.blade.php --}}
@extends('admin.layouts.admin')
@section('title') عمولات الفروع (التارجت) @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="#">العمولات</a> @endsection
@section('startpage') عمولات الفروع @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-store ml-2 text-primary"></i>خطط عمولات الفروع (مبنية على التارجت)
    </h3>
    <div class="card-tools">
      <a href="{{ route('branch_commissions.create') }}" class="btn btn-sm btn-success">
        <i class="fas fa-plus ml-1"></i>خطة جديدة
      </a>
      <a href="{{ route('branch_commissions.targets') }}" class="btn btn-sm btn-warning mr-1">
        <i class="fas fa-bullseye ml-1"></i>أهداف الشهر
      </a>
      <a href="{{ route('branch_commissions.calculate') }}" class="btn btn-sm btn-primary mr-1">
        <i class="fas fa-calculator ml-1"></i>احتساب العمولات
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-2">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger mx-3 mt-2">{{ session('error') }}</div>
  @endif

  {{-- شرح سير العمل --}}
  <div class="alert alert-info mx-3 mt-3 py-2 mb-0">
    <i class="fas fa-info-circle ml-1"></i>
    <strong>سير العمل:</strong>&nbsp;
    ① إنشاء خطة عمولة للفرع وتحديد الشرائح والأعضاء →
    ② <a href="{{ route('branch_commissions.targets') }}">تحديد التارجت الشهري لكل فرع</a> →
    ③ <a href="{{ route('commissions_v2.sales') }}">إدخال مبيعات الموظفين</a> →
    ④ <a href="{{ route('branch_commissions.calculate') }}">احتساب العمولات تلقائياً</a> →
    ⑤ الاعتماد → تدخل مسير الرواتب
  </div>

  <div class="card-body">
    @if($plans->isEmpty())
      <div class="text-center py-5">
        <i class="fas fa-store fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">لا توجد خطط عمولات بعد</h5>
        <a href="{{ route('branch_commissions.create') }}" class="btn btn-success mt-2">
          <i class="fas fa-plus ml-1"></i>إنشاء أول خطة
        </a>
      </div>
    @else
    <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="thead-dark">
        <tr>
          <th>الخطة</th>
          <th>الفرع</th>
          <th>الشرائح</th>
          <th class="text-center">الأعضاء</th>
          <th class="text-center">الحالة</th>
          <th class="text-center" style="width:140px">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($plans as $plan)
        <tr>
          <td>
            <strong>{{ $plan->name }}</strong>
            @if($plan->description)
              <br><small class="text-muted">{{ $plan->description }}</small>
            @endif
          </td>
          <td>
            <i class="fas fa-map-marker-alt text-danger ml-1"></i>
            {{ $plan->branch->branch_name ?? '—' }}
          </td>
          <td style="font-size:.82em">
            @if($plan->tiers)
              @foreach($plan->tiers as $tier)
                <div class="mb-1">
                  <span class="badge badge-secondary">
                    من {{ $tier['from_pct'] }}%
                    @if(!is_null($tier['to_pct'])) إلى {{ $tier['to_pct'] }}% @else فأكثر @endif
                  </span>
                  @if(($tier['seller_rate'] ?? 0) > 0)
                    <span class="badge badge-info">بائع {{ $tier['seller_rate'] }}%</span>
                  @endif
                  @if(($tier['manager_rate'] ?? 0) > 0)
                    <span class="badge badge-primary">مدير {{ $tier['manager_rate'] }}%</span>
                  @endif
                  @if(($tier['seller_rate'] ?? 0) == 0 && ($tier['manager_rate'] ?? 0) == 0)
                    <span class="badge badge-danger">لا توجد عمولة</span>
                  @endif
                </div>
              @endforeach
            @else
              <span class="text-muted">—</span>
            @endif
          </td>
          <td class="text-center">
            <span class="badge badge-dark">{{ $plan->members->count() }} موظف</span>
            <div style="font-size:.78em; margin-top:4px">
              @foreach($plan->members->take(3) as $m)
                <div>
                  {{ $m->employee->employee_name_A ?? '—' }}
                  <span class="badge {{ $m->role === 'manager' ? 'badge-primary' : 'badge-success' }}" style="font-size:.7em">
                    {{ $m->role === 'manager' ? 'مدير' : 'بائع' }}
                  </span>
                  @if($m->also_as_seller)
                    <span class="badge badge-warning" style="font-size:.7em">+ بائع</span>
                  @endif
                </div>
              @endforeach
              @if($plan->members->count() > 3)
                <small class="text-muted">+{{ $plan->members->count() - 3 }} آخرين</small>
              @endif
            </div>
          </td>
          <td class="text-center">
            @if($plan->is_active)
              <span class="badge badge-success">نشطة</span>
            @else
              <span class="badge badge-secondary">معطلة</span>
            @endif
          </td>
          <td class="text-center">
            <a href="{{ route('branch_commissions.edit', $plan->id) }}"
               class="btn btn-xs btn-warning" title="تعديل">
              <i class="fas fa-edit"></i>
            </a>
            <a href="{{ route('branch_commissions.delete', $plan->id) }}"
               class="btn btn-xs btn-danger mr-1"
               onclick="return confirm('هل تريد حذف هذه الخطة؟')" title="حذف">
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
