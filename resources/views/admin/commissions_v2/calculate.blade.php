{{-- FILE: resources/views/admin/commissions_v2/calculate.blade.php --}}
@extends('admin.layouts.admin')
@section('title') احتساب العمولات @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions_v2.rules') }}">قواعد العمولات</a> @endsection
@section('startpage') احتساب @endsection

@section('content')
<div class="col-12">

  {{-- فلتر الشهر --}}
  <div class="d-flex align-items-center mb-3">
    <form method="GET" class="form-inline">
      <select name="month" class="form-control ml-2">
        @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i=>$m)
        <option value="{{ $i+1 }}" {{ $month==$i+1?'selected':'' }}>{{ $m }}</option>
        @endforeach
      </select>
      <input type="number" name="year" class="form-control mr-2 ml-2" style="width:90px" value="{{ $year }}">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
    </form>
    <a href="{{ route('commissions_v2.sales', ['month'=>$month,'year'=>$year]) }}"
       class="btn btn-outline-secondary mr-3">
      <i class="fas fa-arrow-right ml-1"></i>تعديل المبيعات
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(empty($preview))
    <div class="card">
      <div class="card-body text-center py-5">
        <i class="fas fa-calculator fa-3x text-muted mb-3"></i>
        <h5>لا توجد بيانات لاحتساب عمولات هذا الشهر</h5>
        <p class="text-muted">
          تأكد من:
          وجود <a href="{{ route('commissions_v2.rules') }}">قواعد عمولات نشطة</a> و
          <a href="{{ route('commissions_v2.sales', ['month'=>$month,'year'=>$year]) }}">إدخال مبيعات الشهر</a>
        </p>
      </div>
    </div>
  @else

  <form action="{{ route('commissions_v2.confirm') }}" method="POST">
    @csrf
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="year" value="{{ $year }}">

    <div class="card">
      <div class="card-header bg-success text-white">
        <h4 class="mb-0">
          <i class="fas fa-calculator ml-2"></i>
          معاينة العمولات المحتسبة — {{ ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'][$month] }} {{ $year }}
        </h4>
      </div>
      <div class="card-body p-0">
      <div class="table-responsive">
      <table class="table table-bordered mb-0">
        <thead class="thead-dark">
          <tr>
            <th style="width:35px">
              <input type="checkbox" id="selectAll" onchange="toggleAll(this)" title="تحديد الكل">
            </th>
            <th>الموظف</th>
            <th>القاعدة</th>
            <th>أساس الاحتساب</th>
            <th>قيمة المبيعات</th>
            <th>العمولة</th>
          </tr>
        </thead>
        <tbody>
          @php $grandTotal = 0; $rowIndex = 0; @endphp
          @foreach($preview as $empId => $data)
            @foreach($data['commissions'] as $ci => $com)
            @php $rowIndex++; $grandTotal += $com['commission']; @endphp
            <tr>
              <td>
                <input type="checkbox" name="approve[{{ $empId }}][{{ $ci }}][approved]"
                  value="1" class="com-check" checked>
                <input type="hidden" name="approve[{{ $empId }}][{{ $ci }}][rule]"
                  value="{{ $com['rule'] }}">
                <input type="hidden" name="approve[{{ $empId }}][{{ $ci }}][amount]"
                  value="{{ $com['commission'] }}">
              </td>
              <td>
                @if($ci === 0)
                  <strong>{{ $data['employee']->employee_name_A }}</strong>
                  <br><small class="text-muted">{{ $data['employee']->employee_id }}</small>
                @else
                  <small class="text-muted">↑ نفس الموظف</small>
                @endif
              </td>
              <td>{{ $com['rule'] }}</td>
              <td>{{ $com['basis'] }}</td>
              <td>{{ number_format($com['sales_amount'], 2) }} ج.م</td>
              <td class="text-success font-weight-bold">
                {{ number_format($com['commission'], 2) }} ج.م
              </td>
            </tr>
            @endforeach

            {{-- صف الإجمالي للموظف --}}
            @if(count($data['commissions']) > 1)
            <tr class="table-light">
              <td colspan="5" class="text-left font-weight-bold">
                إجمالي عمولات {{ $data['employee']->employee_name_A }}
              </td>
              <td class="text-success font-weight-bold">
                {{ number_format($data['total'], 2) }} ج.م
              </td>
            </tr>
            @endif
          @endforeach
        </tbody>
        <tfoot class="table-success">
          <tr>
            <th colspan="5" class="text-left">إجمالي العمولات</th>
            <th class="text-success">{{ number_format($grandTotal, 2) }} ج.م</th>
          </tr>
        </tfoot>
      </table>
      </div>
      </div>
      <div class="card-footer d-flex justify-content-between align-items-center">
        <div>
          <button type="submit" class="btn btn-success btn-lg">
            <i class="fas fa-check ml-1"></i>اعتماد العمولات المحددة وإضافتها للرواتب
          </button>
          <a href="{{ route('commissions_v2.rules') }}" class="btn btn-secondary mr-2">رجوع</a>
        </div>
        <small class="text-muted">
          <i class="fas fa-info-circle ml-1"></i>
          فقط العمولات المحددة بـ ✓ ستُعتمد وتُضاف لمسير الراتب
        </small>
      </div>
    </div>
  </form>
  @endif
</div>
@endsection

@section('js')
<script>
function toggleAll(masterCb) {
  document.querySelectorAll('.com-check').forEach(cb => cb.checked = masterCb.checked);
}
</script>
@endsection
