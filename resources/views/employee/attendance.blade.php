@php $__title = 'الحضور والانصراف'; @endphp
@include('employee._header')

<div class="card req-card mb-4">
  <div class="card-header bg-light">
    <form method="GET" action="{{ route('employee.attendance') }}" class="form-inline">
      <h5 class="mb-0 ml-auto"><i class="fas fa-clock ml-2 text-info"></i>سجل الحضور</h5>
      <select name="month" class="form-control form-control-sm mr-2">
        @for($m = 1; $m <= 12; $m++)
          <option value="{{ $m }}" @selected($m == $month)>{{ $m }}</option>
        @endfor
      </select>
      <select name="year" class="form-control form-control-sm mr-2">
        @for($y = now()->year - 1; $y <= now()->year; $y++)
          <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
        @endfor
      </select>
      <button type="submit" class="btn btn-sm btn-info">تصفية</button>
    </form>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>التاريخ</th>
            <th>الحضور</th>
            <th>الانصراف</th>
            <th>التأخير</th>
            <th>الحالة</th>
          </tr>
        </thead>
        <tbody>
          @forelse($attendances as $att)
          <tr>
            <td>{{ optional($att->attendance_date)->format('Y-m-d') }}</td>
            <td>{{ $att->check_in_time ?? '—' }}</td>
            <td>{{ $att->check_out_time ?? '—' }}</td>
            <td>{{ $att->late_display }}</td>
            <td>{!! $att->status_label !!}</td>
          </tr>
          @empty
          <tr><td colspan="5" class="text-center text-muted py-3">لا توجد سجلات حضور لهذا الشهر</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="alert alert-info">
  <i class="fas fa-mobile-alt ml-1"></i>
  تسجيل الحضور والانصراف بالبصمة/الوجه من الموبايل والموقع الجغرافي متاح قريباً من تطبيق الموظف — هذه الصفحة للعرض فقط.
</div>

@include('employee._footer')
