{{-- FILE: resources/views/admin/commissions_v2/rules.blade.php --}}
@extends('admin.layouts.admin')
@section('title') قواعد العمولات @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions.index') }}">العمولات</a> @endsection
@section('startpage') القواعد المرنة @endsection

@section('content')
<div class="col-12">
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-percentage ml-2 text-success"></i>قواعد العمولات المرنة
      <a href="{{ route('commissions_v2.create_rule') }}" class="btn btn-sm btn-success mr-2">
        <i class="fas fa-plus"></i> إضافة قاعدة
      </a>
      <a href="{{ route('commissions_v2.sales') }}" class="btn btn-sm btn-info mr-1">
        <i class="fas fa-cash-register"></i> إدخال مبيعات
      </a>
      <a href="{{ route('commissions_v2.calculate') }}" class="btn btn-sm btn-primary mr-1">
        <i class="fas fa-calculator"></i> احتساب العمولات
      </a>
    </h3>
  </div>

  @if(session('success'))
    <div class="alert alert-success mx-3 mt-2">{{ session('success') }}</div>
  @endif

  <div class="card-body">
    {{-- شرح سير العمل --}}
    <div class="alert alert-info py-2 mb-3">
      <i class="fas fa-info-circle ml-1"></i>
      <strong>سير العمل:</strong>
      ①&nbsp;أضف قواعد عمولات ↓
      ②&nbsp;<a href="{{ route('commissions_v2.sales') }}">أدخل مبيعات الشهر</a> ↓
      ③&nbsp;<a href="{{ route('commissions_v2.calculate') }}">احتسب العمولات تلقائياً</a> ↓
      ④ اعتمادها → تدخل مسير الرواتب تلقائياً
    </div>

    @if($rules->isEmpty())
      <div class="text-center py-5">
        <i class="fas fa-percentage fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">لا توجد قواعد عمولات بعد</h5>
        <a href="{{ route('commissions_v2.create_rule') }}" class="btn btn-success mt-2">
          <i class="fas fa-plus ml-1"></i>إضافة أول قاعدة
        </a>
      </div>
    @else
    <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="thead-dark">
        <tr>
          <th>#</th>
          <th>اسم القاعدة</th>
          <th>الكود</th>
          <th>أساس الاحتساب</th>
          <th>المستفيد</th>
          <th>طريقة الحساب</th>
          <th>القيمة</th>
          <th>الفرع</th>
          <th>الحالة</th>
          <th>إجراء</th>
        </tr>
      </thead>
      <tbody>
        @foreach($rules as $rule)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>
            <strong>{{ $rule->name }}</strong>
            @if($rule->description)
              <br><small class="text-muted">{{ $rule->description }}</small>
            @endif
          </td>
          <td><code>{{ $rule->code }}</code></td>
          <td>
            @php
              $basisIcons = [
                'individual_sales' => '👤', 'branch_sales' => '🏢',
                'area_sales'       => '🗺', 'company_sales' => '🏭',
                'fixed'            => '💰', 'kpi_based'    => '📊',
              ];
            @endphp
            {{ $basisIcons[$rule->basis] ?? '' }} {{ $rule->basis_label }}
          </td>
          <td>
            @php
              $recipientLabels = [
                'employee'        => 'موظف فردي',
                'branch_manager'  => 'مدير الفرع',
                'area_manager'    => 'مدير المنطقة',
                'sales_manager'   => 'مدير المبيعات',
                'all_branch'      => 'كل موظفي الفرع',
              ];
            @endphp
            <span class="badge badge-secondary">{{ $recipientLabels[$rule->recipient_type] ?? $rule->recipient_type }}</span>
          </td>
          <td>
            @if($rule->calc_type === 'percentage')
              <span class="badge badge-info">نسبة مئوية</span>
            @elseif($rule->calc_type === 'fixed_amount')
              <span class="badge badge-success">مبلغ ثابت</span>
            @else
              <span class="badge badge-warning">متدرج</span>
            @endif
          </td>
          <td class="text-success font-weight-bold">
            @if($rule->calc_type === 'percentage')
              {{ $rule->percentage }}%
            @elseif($rule->calc_type === 'fixed_amount')
              {{ number_format($rule->fixed_amount, 2) }} ج.م
            @else
              متدرج ({{ count($rule->tiers ?? []) }} شريحة)
            @endif
          </td>
          <td>{{ $rule->branch->branch_name ?? 'كل الفروع' }}</td>
          <td>
            @if($rule->is_active)
              <span class="badge badge-success">نشط</span>
            @else
              <span class="badge badge-secondary">معطل</span>
            @endif
          </td>
          <td>
            <a href="{{ route('commissions_v2.delete_rule', $rule->id) }}"
               class="btn btn-xs btn-danger"
               onclick="return confirm('حذف هذه القاعدة؟')">
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
