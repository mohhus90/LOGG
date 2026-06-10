@extends('admin.layouts.admin')
@section('title') إعداد بيانات ETA @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">الضرائب</a> @endsection
@section('startpage') بيانات الاعتماد @endsection

@section('content')
<div class="col-12 col-md-9 offset-md-1">

  {{-- شرح كيفية الحصول على البيانات --}}
  <div class="card card-outline card-warning mb-3">
    <div class="card-header" data-card-widget="collapse" style="cursor:pointer">
      <h3 class="card-title">
        <i class="fas fa-question-circle text-warning ml-2"></i>
        كيف تحصل على Client ID و Client Secret؟ <small class="text-muted">(اضغط للتوسيع)</small>
      </h3>
      <div class="card-tools"><button type="button" class="btn btn-tool"><i class="fas fa-plus"></i></button></div>
    </div>
    <div class="card-body" style="display:none">
      <ol class="mb-0">
        <li class="mb-2">سجّل دخولك على <a href="https://invoicing.eta.gov.eg" target="_blank"><strong>invoicing.eta.gov.eg</strong></a> بإيميلك وكلمة مرورك المعتادة</li>
        <li class="mb-2">من القائمة العلوية اختر <strong>الإعدادات</strong> أو <strong>Settings</strong></li>
        <li class="mb-2">اختر <strong>تكامل ERP</strong> أو <strong>ERP Integration</strong></li>
        <li class="mb-2">اضغط <strong>إنشاء اعتمادات جديدة</strong> أو <strong>Generate New Credentials</strong></li>
        <li class="mb-2">انسخ الـ <strong>Client ID</strong> والـ <strong>Client Secret</strong> من هناك والصقهم هنا</li>
      </ol>
      <div class="alert alert-danger mt-3 mb-0 py-2">
        <i class="fas fa-exclamation-triangle ml-1"></i>
        <strong>تنبيه:</strong> إيميلك وكلمة مرور البوابة <u>لا تعمل هنا</u> — يجب استخدام بيانات اعتماد ERP المولّدة من داخل البوابة
      </div>
    </div>
  </div>

  <div class="card card-outline card-dark">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-key ml-2"></i>
        بيانات اعتماد ERP — منظومة الفواتير الإلكترونية
      </h3>
    </div>
    <div class="card-body">

      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      <form action="{{ route('tax.credentials.save') }}" method="POST" id="credForm">
        @csrf
        <input type="hidden" name="auth_type" value="api">

        {{-- Client ID --}}
        <div class="form-group">
          <label class="font-weight-bold">Client ID <span class="text-danger">*</span></label>
          <input type="text" name="client_id"
            class="form-control font-size-sm @error('client_id') is-invalid @enderror"
            value="{{ old('client_id', $credential?->client_id) }}"
            placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
            autocomplete="off" required
            style="font-family:monospace;letter-spacing:1px">
          @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Client Secret --}}
        <div class="form-group">
          <label class="font-weight-bold">
            Client Secret <span class="text-danger">*</span>
            @if($credential && $credential->client_secret)
              <span class="badge badge-success mr-2">
                <i class="fas fa-lock ml-1"></i>محفوظ في قاعدة البيانات
              </span>
            @endif
          </label>
          <div class="input-group">
            <input type="password" id="secretInput" name="client_secret"
              class="form-control @error('client_secret') is-invalid @enderror"
              placeholder="{{ $credential && $credential->client_secret ? '● ● ● محفوظ — اتركه فارغاً للإبقاء عليه' : 'أدخل Client Secret' }}"
              autocomplete="new-password"
              {{ ($credential && $credential->client_secret) ? '' : 'required' }}>
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-secondary" id="eyeBtn" title="إظهار/إخفاء">
                <i class="fas fa-eye" id="eyeIcon"></i>
              </button>
            </div>
          </div>
          @if($credential && $credential->client_secret)
            <small class="text-muted">
              <i class="fas fa-info-circle ml-1"></i>
              القيمة محفوظة بأمان — اتركه فارغاً للإبقاء على القيمة الحالية أو أدخل قيمة جديدة لتحديثه
            </small>
          @endif
          @error('client_secret')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        {{-- بيانات المنشأة --}}
        <hr>
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>الرقم الضريبي للمنشأة</label>
              <input type="text" name="taxpayer_id" class="form-control"
                value="{{ old('taxpayer_id', $credential?->taxpayer_id) }}"
                placeholder="مثال: 123456789" autocomplete="off">
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>اسم المنشأة (كما في الضرائب)</label>
              <input type="text" name="taxpayer_name" class="form-control"
                value="{{ old('taxpayer_name', $credential?->taxpayer_name) }}"
                placeholder="اسم الشركة باللغة العربية" autocomplete="off">
            </div>
          </div>
        </div>

        {{-- حالة التوكن --}}
        @if($credential)
        <div class="form-group mb-0">
          <label>حالة الاتصال</label>
          <div>
            @if($credential->isTokenValid())
              <span class="badge badge-success p-2">
                <i class="fas fa-wifi ml-1"></i>
                متصل — التوكن صالح حتى {{ $credential->token_expires_at?->format('Y-m-d H:i') }}
              </span>
            @else
              <span class="badge badge-secondary p-2">
                <i class="fas fa-clock ml-1"></i>
                غير متصل — سيتجدد تلقائياً عند أول طلب
              </span>
            @endif
          </div>
        </div>
        @endif

        <div class="mt-3 d-flex flex-wrap" style="gap:8px">
          <button type="submit" class="btn btn-dark">
            <i class="fas fa-save ml-1"></i> حفظ البيانات
          </button>
          @if($credential)
          <button type="button" class="btn btn-info" id="testBtn">
            <i class="fas fa-plug ml-1"></i> اختبار الاتصال وتشخيص المشكلة
          </button>
          @endif
          <a href="{{ route('tax.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>

        {{-- نتيجة الاختبار --}}
        <div id="testResult" class="mt-3"></div>

      </form>
    </div>
  </div>

</div>
@endsection

@section('script')
<script>
// زر العين — إظهار/إخفاء
document.getElementById('eyeBtn').addEventListener('click', function () {
    var inp = document.getElementById('secretInput');
    var ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        ico.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

@if($credential)
document.getElementById('testBtn').addEventListener('click', function () {
    var btn = this;
    var res = document.getElementById('testResult');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i> جارٍ الاختبار...';
    res.innerHTML = '';

    fetch('{{ route("tax.test_connection") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(function(r) {
        // نقرأ النص الخام أولاً
        return r.text().then(function(text) {
            return { status: r.status, text: text };
        });
    })
    .then(function(obj) {
        var status = obj.status;
        var text   = obj.text;

        // حاول تحليل الـ JSON
        var d = null;
        try { d = JSON.parse(text); } catch(e) { d = null; }

        if (d !== null) {
            // استجابة JSON صحيحة
            if (d.success) {
                res.innerHTML = '<div class="alert alert-success">' + d.message + '</div>';
            } else {
                var extra = '';
                if (d.grant_types) extra = '<hr><small>Grant types مدعومة: <code>' + d.grant_types + '</code></small>';
                if (d.api_url)     extra += '<br><small>API URL: <code>' + d.api_url + '</code></small>';
                res.innerHTML = '<div class="alert alert-danger"><strong>تفاصيل الخطأ:</strong><br>' + (d.message || text) + extra + '</div>';
            }
        } else {
            // استجابة غير JSON — نعرض الـ HTML كنص لمعرفة السبب
            var preview = text.substring(0, 500).replace(/</g,'&lt;').replace(/>/g,'&gt;');
            res.innerHTML = '<div class="alert alert-danger">'
                + '<strong>خطأ HTTP ' + status + '</strong><br>'
                + '<small>الاستجابة (أول 500 حرف):</small><br>'
                + '<pre style="font-size:11px;max-height:150px;overflow:auto;background:#f8f9fa;padding:8px">' + preview + '</pre>'
                + '</div>';
        }
    })
    .catch(function(err) {
        res.innerHTML = '<div class="alert alert-danger">خطأ في الاتصال: ' + err.message + '</div>';
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug ml-1"></i> اختبار الاتصال وتشخيص المشكلة';
    });
});
@endif
</script>
@endsection
