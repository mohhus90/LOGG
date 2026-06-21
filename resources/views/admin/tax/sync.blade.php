@extends('admin.layouts.admin')
@section('title') سحب الفواتير من ETA @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">الضرائب</a> @endsection
@section('startpage') سحب الفواتير @endsection

@section('content')
<div class="col-12 col-md-7 offset-md-2">
  <div class="card card-outline card-dark">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-cloud-download-alt ml-2"></i>
        سحب الفواتير من منظومة ETA
      </h3>
    </div>
    <div class="card-body">

      @if(session('error'))
        <div class="alert alert-danger" style="white-space:pre-line">{{ session('error') }}</div>
      @endif
      @if(session('warning'))
        <div class="alert alert-warning" style="white-space:pre-line">{{ session('warning') }}</div>
      @endif

      <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle ml-1"></i>
        الرقم الضريبي: <strong>{{ $credential->taxpayer_id ?? '—' }}</strong>
        &nbsp;|&nbsp; المنشأة: <strong>{{ $credential->taxpayer_name ?? '—' }}</strong>
      </div>

      <form action="{{ route('tax.sync') }}" method="POST" id="syncForm">
        @csrf
        <div class="form-group">
          <label>نوع الفواتير <span class="text-danger">*</span></label>
          <select name="direction" class="form-control" required>
            <option value="Both">مبيعات + مشتريات</option>
            <option value="Sent">مبيعات فقط</option>
            <option value="Received">مشتريات فقط</option>
          </select>
        </div>

        <div class="form-group">
          <label>تصفية بتاريخ <span class="text-danger">*</span></label>
          <select name="date_type" class="form-control">
            <option value="issue">تاريخ الإصدار (dateTimeIssued)</option>
            <option value="submission">تاريخ الإرسال لـ ETA (submissionDate) — جرّب هذا إذا لم تجد نتائج</option>
          </select>
          <small class="text-muted">إذا رجعت صفر فواتير بتاريخ الإصدار، جرّب تاريخ الإرسال</small>
        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>من تاريخ <span class="text-danger">*</span></label>
              <input type="date" name="from" class="form-control @error('from') is-invalid @enderror"
                value="{{ old('from', now()->startOfMonth()->format('Y-m-d')) }}" required>
              @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>إلى تاريخ <span class="text-danger">*</span></label>
              <input type="date" name="to" class="form-control @error('to') is-invalid @enderror"
                value="{{ old('to', now()->format('Y-m-d')) }}" required>
              @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        {{-- خيار سحب التفاصيل --}}
        <div class="card card-body bg-light mb-3 p-3">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="fetch_details" name="fetch_details" value="1"
              {{ old('fetch_details') ? 'checked' : '' }}>
            <label class="custom-control-label font-weight-bold" for="fetch_details">
              سحب تفاصيل كل فاتورة (أصناف + ضرائب تفصيلية)
            </label>
          </div>
          <small class="text-muted mt-1 d-block">
            <i class="fas fa-exclamation-circle text-warning ml-1"></i>
            سيستغرق وقتاً أطول — طلب API منفصل لكل فاتورة. الفواتير التي يفشل سحب تفاصيلها تُحفظ بدون أصناف.
          </small>
        </div>

        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle ml-1"></i>
          الفواتير المرحّلة محاسبياً لن يتم تحديثها. الفواتير الجديدة ستُضاف تلقائياً.
        </div>

        <div id="loadingMsg" class="text-center d-none py-3">
          <div class="spinner-border text-dark ml-2" role="status"></div>
          <span id="loadingText">جارٍ السحب من ETA... قد يستغرق بعض الوقت</span>
        </div>

        <button type="submit" class="btn btn-dark" id="syncBtn">
          <i class="fas fa-cloud-download-alt ml-1"></i> بدء السحب
        </button>
        <a href="{{ route('tax.index') }}" class="btn btn-secondary mr-1">إلغاء</a>
      </form>
    </div>
  </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('syncForm').addEventListener('submit', function() {
    document.getElementById('syncBtn').disabled = true;
    document.getElementById('loadingMsg').classList.remove('d-none');
    if (document.getElementById('fetch_details').checked) {
        document.getElementById('loadingText').textContent =
            'جارٍ السحب من ETA وتحميل التفاصيل... قد يستغرق عدة دقائق حسب عدد الفواتير';
    }
});
</script>
@endsection
