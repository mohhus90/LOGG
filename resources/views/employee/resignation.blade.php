@php $__title = 'طلب استقالة'; @endphp
@include('employee._header')

<div class="row">
  <div class="col-md-6">
    <div class="card req-card mb-4">
      <div class="card-header" style="background:#dc3545;color:#fff;border-radius:12px 12px 0 0">
        <h5 class="mb-0"><i class="fas fa-door-open ml-2"></i>تقديم طلب استقالة</h5>
      </div>
      <div class="card-body">
        @if($resignation && $resignation->status === 0)
          <div class="alert alert-warning">
            لديك طلب استقالة قيد الانتظار بتاريخ ترك عمل مقترح
            <strong>{{ optional($resignation->start_date)->format('Y-m-d') }}</strong>.
          </div>
        @else
          <form method="POST" action="{{ route('employee.resignation.store') }}">
            @csrf
            <div class="form-group">
              <label>آخر يوم عمل مقترح <span class="text-danger">*</span></label>
              <input type="date" name="last_working_date" class="form-control" required
                min="{{ today()->format('Y-m-d') }}" value="{{ old('last_working_date') }}">
            </div>
            <div class="form-group">
              <label>سبب الاستقالة</label>
              <textarea name="reason" class="form-control" rows="3" placeholder="اذكر سبب الاستقالة...">{{ old('reason') }}</textarea>
            </div>
            <button type="submit" class="btn btn-block text-white" style="background:#dc3545;border-radius:8px"
              onclick="return confirm('هل أنت متأكد من تقديم طلب الاستقالة؟')">
              <i class="fas fa-paper-plane ml-2"></i>إرسال طلب الاستقالة
            </button>
          </form>
        @endif
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card req-card mb-4">
      <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-history ml-2 text-muted"></i>آخر حالة</h5>
      </div>
      <div class="card-body">
        @if($resignation)
          <p><strong>تاريخ الطلب:</strong> {{ $resignation->request_date }}</p>
          <p><strong>آخر يوم عمل مقترح:</strong> {{ optional($resignation->start_date)->format('Y-m-d') }}</p>
          <p><strong>الحالة:</strong> {!! $resignation->status_label !!}</p>
          @if($resignation->review_notes)
            <p><strong>ملاحظات الإدارة:</strong> {{ $resignation->review_notes }}</p>
          @endif
        @else
          <p class="text-muted mb-0">لم تقدم أي طلب استقالة من قبل.</p>
        @endif
      </div>
    </div>
  </div>
</div>

@include('employee._footer')
