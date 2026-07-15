@php $__title = 'شهادة راتب'; @endphp
@include('employee._header')

@php
  $status = match(true) {
      !$latest => 'none',
      $latest->status === 0 => 'pending',
      $latest->status === 1 => 'approved',
      default => 'none',
  };
@endphp

<div class="card req-card mb-4">
  <div class="card-header bg-light">
    <h5 class="mb-0"><i class="fas fa-file-signature ml-2 text-primary"></i>شهادة راتب (HR Letter)</h5>
  </div>
  <div class="card-body">
    @if($status === 'approved')
      <p class="text-success"><i class="fas fa-check-circle ml-1"></i>تمت الموافقة على طلبك، يمكنك تحميل الشهادة الآن.</p>
      <a href="{{ route('employee.letters.salary_certificate.download') }}" class="btn text-white" style="background:#11998e;border-radius:8px">
        <i class="fas fa-download ml-2"></i>تحميل شهادة الراتب
      </a>
    @elseif($status === 'pending')
      <p class="text-warning"><i class="fas fa-hourglass-half ml-1"></i>طلبك قيد الانتظار، بانتظار موافقة المسؤول.</p>
      <p><small class="text-muted">السبب المذكور: {{ $latest->reason }}</small></p>
    @else
      @if($latest && $latest->status === 2)
        <div class="alert alert-danger py-2">
          تم رفض طلبك السابق. {{ $latest->review_notes ? '(السبب: ' . $latest->review_notes . ')' : '' }}
          يمكنك تقديم طلب جديد.
        </div>
      @endif
      <p class="text-muted">شهادة الراتب تحتاج موافقة مسؤول قبل التنزيل. من فضلك اذكر سبب الطلب.</p>
      <form method="POST" action="{{ route('employee.letters.salary_certificate.request_access') }}">
        @csrf
        <div class="form-group">
          <label>سبب الطلب <span class="text-danger">*</span></label>
          <textarea name="reason" class="form-control" rows="2" required
            placeholder="مثال: لتقديمها لجهة حكومية / بنك / إلخ">{{ old('reason') }}</textarea>
        </div>
        <button type="submit" class="btn text-white" style="background:#11998e;border-radius:8px">
          <i class="fas fa-paper-plane ml-2"></i>إرسال الطلب
        </button>
      </form>
    @endif
  </div>
</div>

@include('employee._footer')
