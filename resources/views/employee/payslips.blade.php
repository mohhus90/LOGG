@php $__title = 'قسائم الراتب'; @endphp
@include('employee._header')

<div class="card req-card mb-4">
  <div class="card-header bg-light">
    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar ml-2 text-success"></i>قسائم الراتب المعتمدة</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-sm mb-0">
        <thead class="thead-light">
          <tr>
            <th>الشهر</th>
            <th>صافي الراتب</th>
            <th>الحالة</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @forelse($payslips as $p)
          <tr>
            <td>{{ $p->month_name }} {{ $p->year }}</td>
            <td>{{ number_format($p->net_salary, 2) }} جنيه</td>
            <td>{!! $p->status_label !!}</td>
            <td>
              <a href="{{ route('employee.payslips.pdf', $p->id) }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-download ml-1"></i>تحميل PDF
              </a>
            </td>
          </tr>
          @empty
          <tr><td colspan="4" class="text-center text-muted py-3">لا توجد قسائم راتب معتمدة بعد</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@include('employee._footer')
