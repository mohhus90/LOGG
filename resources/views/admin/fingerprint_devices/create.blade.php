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

                <div class="row">
                    <div class="col-md-5 form-group">
                        <label>عنوان IP <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                            </div>
                            <input type="text" name="ip_address" class="form-control" required
                                placeholder="192.168.1.100"
                                value="{{ old('ip_address', $device->ip_address ?? '') }}">
                        </div>
                        <small class="text-muted">عنوان IP الجهاز على الشبكة المحلية</small>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>البورت <span class="text-danger">*</span></label>
                        <input type="number" name="port" class="form-control" required
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

                <div class="row">
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

@section('js')
<script>
const portDefaults = { zkteco:4370, suprema:4370, anviz:5010, hikvision:80, dahua:80, generic:8080 };
const portHints    = {
    zkteco:    'ZKTeco افتراضي: 4370',
    suprema:   'Suprema افتراضي: 4370',
    anviz:     'Anviz افتراضي: 5010',
    hikvision: 'Hikvision HTTP افتراضي: 80',
    dahua:     'Dahua HTTP افتراضي: 80',
    generic:   'HTTP Webhook افتراضي: 8080',
};

function selectProtocol(val, el) {
    document.querySelectorAll('.protocol-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('protocolInput').value = val;
    document.getElementById('portInput').value      = portDefaults[val] || 4370;
    document.getElementById('portHint').textContent = portHints[val] || '';

    // عرض التلميح المناسب
    const isHttp = ['hikvision','dahua','generic'].includes(val);
    document.getElementById('zktecoTip').classList.toggle('d-none', isHttp);
    document.getElementById('httpTip').classList.toggle('d-none', !isHttp);
}

@if(isset($device))
function testConnectionInline(id, btn) {
    const original = btn.innerHTML;
    btn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled   = true;

    fetch(`{{ url('admin/dashboard/fingerprint_devices') }}/${id}/test`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        const result = document.getElementById('testResult');
        result.className  = 'badge badge-' + (data.success ? 'success' : 'danger') + ' mr-2';
        result.textContent = data.message;
    })
    .catch(() => { document.getElementById('testResult').textContent = 'خطأ'; })
    .finally(() => { btn.innerHTML = original; btn.disabled = false; });
}
@endif
</script>
@endsection
