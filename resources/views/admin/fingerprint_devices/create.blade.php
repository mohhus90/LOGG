@extends('admin.layouts.admin')
@section('title') {{ isset($device) ? 'تعديل جهاز البصمة' : 'إضافة جهاز بصمة' }} @endsection
@section('start') الحضور والانصراف @endsection
@section('home') <a href="{{ route('fingerprint_devices.index') }}">أجهزة البصمة</a> @endsection
@section('startpage') {{ isset($device) ? 'تعديل' : 'إضافة' }} @endsection

@section('css')
<style>
.protocol-card { cursor:pointer; border:2px solid #dee2e6; border-radius:8px; padding:12px; transition:.2s; }
.protocol-card:hover, .protocol-card.selected { border-color:#007bff; background:#f0f7ff; }
.protocol-card .protocol-name { font-weight:600; font-size:.9em; }
.protocol-card .protocol-desc { font-size:.78em; color:#666; }
.protocol-icon { font-size:1.6em; margin-bottom:4px; }
#agentSection code { direction:ltr; unicode-bidi:embed; display:inline-block; }
</style>
@endsection

@section('content')
<div class="col-md-9 mx-auto">
    <div class="card card-{{ isset($device) ? 'warning' : 'success' }}">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-fingerprint ml-2"></i>
                {{ isset($device) ? 'تعديل جهاز: '.$device->device_name : 'إضافة جهاز بصمة جديد' }}
            </h3>
        </div>

        <form action="{{ isset($device) ? route('fingerprint_devices.update', $device->id) : route('fingerprint_devices.store') }}"
              method="POST" id="deviceForm">
            @csrf
            @if(isset($device)) @method('PUT') @endif

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                {{-- ── بروتوكول الجهاز ── --}}
                <div class="form-group">
                    <label class="font-weight-bold mb-2">نوع/بروتوكول الجهاز <span class="text-danger">*</span></label>
                    <div class="row">
                        @foreach([
                            ['zkteco',    'fas fa-fingerprint', 'ZKTeco / ZKLib', 'TCP 4370 — الأكثر شيوعاً في السوق المصري', '#28a745'],
                            ['suprema',   'fas fa-shield-alt',  'Suprema',        'TCP — أجهزة Suprema BioStation', '#17a2b8'],
                            ['anviz',     'fas fa-id-card',     'Anviz',          'TCP — أجهزة Anviz C2/W1/EP300', '#6610f2'],
                            ['hikvision', 'fas fa-video',       'Hikvision',      'HTTP REST API — كاميرات وأجهزة Hikvision', '#dc3545'],
                            ['dahua',     'fas fa-camera',      'Dahua',          'HTTP REST API — أجهزة Dahua', '#fd7e14'],
                            ['generic',   'fas fa-plug',        'Generic Webhook','الجهاز يرسل بيانات إلى السيرفر', '#6c757d'],
                            ['agent',     'fas fa-cloud-upload-alt', 'Agent — فرع بعيد', 'فرع على شبكة مختلفة — يرسل عبر الإنترنت', '#6f42c1'],
                        ] as [$val, $icon, $name, $desc, $color])
                        <div class="col-md-4 mb-2">
                            <div class="protocol-card {{ (old('protocol', $device->protocol ?? 'zkteco')) == $val ? 'selected' : '' }}"
                                 onclick="selectProtocol('{{ $val }}', this)">
                                <div class="text-center">
                                    <div class="protocol-icon" style="color:{{ $color }}">
                                        <i class="{{ $icon }}"></i>
                                    </div>
                                    <div class="protocol-name">{{ $name }}</div>
                                    <div class="protocol-desc">{{ $desc }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="protocol" id="protocolInput"
                        value="{{ old('protocol', $device->protocol ?? 'zkteco') }}">
                </div>

                <hr>

                {{-- ── معلومات الجهاز ── --}}
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>اسم الجهاز <span class="text-danger">*</span></label>
                        <input type="text" name="device_name" class="form-control" required
                            placeholder="مثال: بصمة باب الدخول الرئيسي"
                            value="{{ old('device_name', $device->device_name ?? '') }}">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>كود الجهاز <span class="text-danger">*</span></label>
                        <input type="text" name="device_code" class="form-control" required
                            placeholder="مثال: DEV001"
                            value="{{ old('device_code', $device->device_code ?? '') }}">
                    </div>
                </div>

                {{-- ── قسم الشبكة (مخفي لبروتوكول Agent) ── --}}
                <div id="networkSection">
                <div class="row">
                    <div class="col-md-5 form-group">
                        <label>عنوان IP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                            </div>
                            <input type="text" name="ip_address" id="ipAddressInput" class="form-control"
                                placeholder="192.168.1.100"
                                value="{{ old('ip_address', $device->ip_address ?? '') }}">
                        </div>
                        <small class="text-muted">عنوان IP الجهاز على الشبكة المحلية</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>البورت <span class="text-danger">*</span></label>
                        <input type="number" name="port" class="form-control"
                            id="portInput"
                            value="{{ old('port', $device->port ?? 4370) }}">
                        <small class="text-muted" id="portHint">ZKTeco افتراضي: 4370</small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>كلمة مرور الجهاز</label>
                        <input type="text" name="device_password" class="form-control"
                            placeholder="اتركه فارغاً إن لم توجد"
                            value="{{ old('device_password') }}">
                        <small class="text-muted">كلمة مرور الجهاز (إن وُجدت)</small>
                    </div>
                </div>
                </div>{{-- end networkSection --}}

                {{-- ── قسم Agent (ظاهر فقط لبروتوكول Agent) ── --}}
                <div id="agentSection" class="d-none">
                    <div class="alert alert-purple" style="background:#f3e8ff;border:1px solid #c084fc;border-radius:8px;padding:16px">
                        <h6><i class="fas fa-cloud-upload-alt ml-1" style="color:#6f42c1"></i>إعداد Agent للفرع البعيد</h6>
                        <p class="mb-2">بعد الحفظ ستحصل على <strong>API Token</strong> خاص بهذا الفرع. ثم:</p>
                        <ol class="mb-0" style="font-size:.9em">
                            <li>حمّل مجلد <strong>branch-agent</strong> على كمبيوتر الفرع</li>
                            <li>انسخ التوكن في ملف <code>config.php</code></li>
                            <li>شغّل الأمر <code>composer install</code> مرة واحدة</li>
                            <li>جدوِل تشغيل <code>php agent.php</code> بشكل دوري (كل 30 دقيقة مثلاً)</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label class="font-weight-bold">الفرع الأساسي للجهاز <span class="text-danger">*</span></label>
                        <select name="branches_id" class="form-control">
                            <option value="">— اختر الفرع —</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branches_id', $device->branches_id ?? '') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">الفرع الذي يقع فيه الجهاز فعلياً</small>
                    </div>
                    <div class="col-md-8 form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-code-branch ml-1 text-info"></i>
                            فروع إضافية يخدمها هذا الجهاز
                        </label>
                        <div class="border rounded p-2" style="max-height:120px;overflow-y:auto;background:#fafbfc">
                            @foreach($branches as $branch)
                            <div class="form-check form-check-inline mb-1">
                                <input class="form-check-input" type="checkbox"
                                       name="extra_branch_ids[]"
                                       value="{{ $branch->id }}"
                                       id="extra_branch_c{{ $branch->id }}"
                                       {{ in_array($branch->id, array_map('intval', old('extra_branch_ids', []))) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="extra_branch_c{{ $branch->id }}">
                                    {{ $branch->branch_name }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                        <small class="text-info">
                            <i class="fas fa-info-circle ml-1"></i>
                            اختر الفروع التي يبصم فيها موظفوها على هذا الجهاز
                        </small>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>موقع الجهاز</label>
                        <input type="text" name="location" class="form-control"
                            placeholder="مثال: الطابق الأول — مدخل A"
                            value="{{ old('location', $device->location ?? '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>موديل الجهاز</label>
                        <input type="text" name="model" class="form-control"
                            placeholder="مثال: ZKTeco K40 Pro"
                            value="{{ old('model', $device->model ?? '') }}">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>الرقم التسلسلي</label>
                        <input type="text" name="serial_number" class="form-control"
                            placeholder="Serial Number"
                            value="{{ old('serial_number', $device->serial_number ?? '') }}">
                    </div>
                </div>

                @if(isset($device))
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control" style="max-width:200px">
                        <option value="1" {{ $device->status==1?'selected':'' }}>نشط</option>
                        <option value="2" {{ $device->status==2?'selected':'' }}>معطل</option>
                    </select>
                </div>
                @endif

                {{-- نصيحة ZKTeco --}}
                <div class="alert alert-info mt-2" id="zktecoTip">
                    <i class="fas fa-lightbulb ml-1"></i>
                    <strong>ZKTeco:</strong> تأكد من تفعيل TCP/IP في إعدادات الجهاز وأن الجهاز والسيرفر على نفس الشبكة.
                    البورت الافتراضي <strong>4370</strong>. استخدم زر "اختبار الاتصال" بعد الحفظ للتحقق.
                </div>
                <div class="alert alert-warning mt-2 d-none" id="httpTip">
                    <i class="fas fa-lightbulb ml-1"></i>
                    <strong>HTTP API:</strong> تأكد من إعدادات المستخدم والصلاحيات في الجهاز.
                    البورت الافتراضي <strong>80</strong> أو <strong>8080</strong>.
                </div>

            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-{{ isset($device) ? 'warning' : 'success' }}">
                    <i class="fas fa-save ml-1"></i>
                    {{ isset($device) ? 'حفظ التعديلات' : 'إضافة الجهاز' }}
                </button>
                <a href="{{ route('fingerprint_devices.index') }}" class="btn btn-secondary mr-2">رجوع</a>

                @if(isset($device))
                <button type="button" class="btn btn-info mr-2"
                    onclick="testConnectionInline({{ $device->id }}, this)">
                    <i class="fas fa-plug ml-1"></i> اختبار الاتصال الآن
                </button>
                <span id="testResult" class="mr-2"></span>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script>
/* ── إصلاح اختيار بروتوكول جهاز البصمة ── */

var portDefaults  = { zkteco:4370, suprema:4370, anviz:5010, hikvision:80, dahua:80, generic:8080, agent:0 };
var portHintTexts = {
  zkteco:    'ZKTeco / ZKLib — البورت الافتراضي: 4370',
  suprema:   'Suprema BioStation — البورت الافتراضي: 4370',
  anviz:     'Anviz — البورت الافتراضي: 5010',
  hikvision: 'Hikvision HTTP REST — البورت الافتراضي: 80',
  dahua:     'Dahua HTTP REST — البورت الافتراضي: 80',
  generic:   'Generic HTTP Webhook — البورت الافتراضي: 8080',
  agent:     'Agent — لا يحتاج IP/Port',
};

function applyProtocolUI(val) {
  document.getElementById('protocolInput').value = val;

  var portInp     = document.getElementById('portInput');
  var portHint    = document.getElementById('portHint');
  var networkSec  = document.getElementById('networkSection');
  var agentSec    = document.getElementById('agentSection');
  var zkTip       = document.getElementById('zktecoTip');
  var httpTip     = document.getElementById('httpTip');

  var isAgent = val === 'agent';
  var isHttp  = ['hikvision','dahua','generic'].indexOf(val) !== -1;

  if (networkSec) networkSec.classList.toggle('d-none', isAgent);
  if (agentSec)   agentSec.classList.toggle('d-none',  !isAgent);
  if (zkTip)      zkTip.classList.toggle('d-none',  isHttp || isAgent);
  if (httpTip)    httpTip.classList.toggle('d-none', !isHttp || isAgent);

  if (!isAgent) {
    if (portInp)  portInp.value       = portDefaults[val]  || 4370;
    if (portHint) portHint.textContent = portHintTexts[val] || '';
  }
}

function selectProtocol(val, el) {
  document.querySelectorAll('.protocol-card').forEach(function(card) {
    card.classList.remove('selected');
    card.style.borderColor = '#dee2e6';
    card.style.background  = '';
  });
  el.classList.add('selected');
  el.style.borderColor = '#007bff';
  el.style.background  = '#f0f7ff';
  applyProtocolUI(val);
}

document.addEventListener('DOMContentLoaded', function() {
  var currentProtocol = document.getElementById('protocolInput').value || 'zkteco';
  var activeCard = document.querySelector('.protocol-card[onclick*="' + currentProtocol + '"]');
  if (activeCard) {
    activeCard.classList.add('selected');
    activeCard.style.borderColor = '#007bff';
    activeCard.style.background  = '#f0f7ff';
  }
  applyProtocolUI(currentProtocol);
});

// اختبار الاتصال في صفحة التعديل
function testConnectionInline(id, btn) {
  var original = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  btn.disabled  = true;

  fetch('/admin/dashboard/fingerprint_devices/' + id + '/test', {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    var result    = document.getElementById('testResult');
    if (result) {
      result.className   = 'badge badge-' + (data.success ? 'success' : 'danger') + ' mr-2 p-2';
      result.textContent = data.message;
    }
  })
  .catch(function() {
    var result = document.getElementById('testResult');
    if (result) { result.className = 'badge badge-danger mr-2'; result.textContent = 'خطأ في الاتصال'; }
  })
  .finally(function() { btn.innerHTML = original; btn.disabled = false; });
}
</script>
@endsection
