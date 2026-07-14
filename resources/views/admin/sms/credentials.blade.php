@extends('admin.layouts.admin')
@section('title') إرسال بيانات الدخول SMS @endsection
@section('start') الرسائل القصيرة @endsection
@section('home') <a href="{{ route('sms.compose') }}">SMS</a> @endsection
@section('startpage') إرسال بيانات الدخول @endsection

@section('css')
<style>
.filter-card   { background:#fff; border-radius:10px; padding:16px; box-shadow:0 2px 12px rgba(0,0,0,.07); border-right:4px solid #2d5a9e; margin-bottom:1rem; }
.compose-card  { background:#fff; border-radius:10px; padding:16px; box-shadow:0 2px 12px rgba(0,0,0,.07); border-right:4px solid #28a745; margin-bottom:1rem; }
.results-card  { background:#fff; border-radius:10px; padding:16px; box-shadow:0 2px 12px rgba(0,0,0,.07); border-right:4px solid #ffc107; margin-bottom:1rem; }
.emp-table th  { background:#1e3a5f; color:#fff; font-size:.82rem; white-space:nowrap; }
.emp-table td  { font-size:.83rem; vertical-align:middle; }
.stat-box      { text-align:center; border-radius:8px; padding:12px; }
.char-count    { font-size:.78rem; color:#6c757d; text-align:left; }
.badge-sent    { background:#28a745; color:#fff; }
.badge-failed  { background:#dc3545; color:#fff; }
.badge-nophone { background:#6c757d; color:#fff; }
.badge-nocred  { background:#e0a800; color:#fff; }
#empTableBody tr:hover { background:#f8f9fa; }
.placeholder-btn { font-family:monospace; }
</style>
@endsection

@section('content')
<div class="col-12">

{{-- تحذير SMS معطّل --}}
@if(!$smsEnabled)
<div class="alert alert-warning alert-dismissible">
  <button type="button" class="close" data-dismiss="alert">&times;</button>
  <i class="fas fa-exclamation-triangle ml-1"></i>
  <strong>خدمة SMS غير مفعّلة.</strong>
  <a href="{{ route('generalsetting.edit') }}" class="btn btn-sm btn-warning mr-2">
    <i class="fas fa-cog ml-1"></i> اذهب إلى الإعدادات
  </a>
</div>
@endif

<div class="alert alert-info">
  <i class="fas fa-info-circle ml-1"></i>
  هذه الشاشة تبعت <strong>رسالة مختلفة لكل موظف</strong> (تحتوي على اسم المستخدم وكلمة المرور الخاصة به).
  لأن بوابة SMS تفرض فترة تهدئة بين الرسائل المتتالية، سيتم الإرسال <strong>واحدة تلو الأخرى بفاصل زمني</strong> بدلاً من دفعة واحدة —
  يُرجى إبقاء الصفحة مفتوحة حتى انتهاء الإرسال.
</div>

{{-- ══ بطاقة نص الرسالة ══ --}}
<div class="compose-card">
  <h5 class="font-weight-bold mb-3" style="color:#155724">
    <i class="fas fa-pen-alt ml-2"></i>كتابة الرسالة
  </h5>
  <div class="form-group mb-1">
    <textarea id="smsMessage" class="form-control" rows="4" maxlength="800" oninput="updateCharCount()"
      placeholder="Example: Hello {name_en}, your app login details: [app link] — Username: {username} — Password: {password}">Hello {name_en}, your app login details:
Username: {username}
Password: {password}</textarea>
  </div>
  <div class="d-flex justify-content-between align-items-center flex-wrap">
    <small class="char-count" id="charCount">0 / 800 حرف</small>
    <div class="mt-1">
      <span class="small text-muted ml-1">إدراج متغيّر:</span>
      <button type="button" class="btn btn-sm btn-outline-primary placeholder-btn" onclick="insertPlaceholder('{name}')">{name}</button>
      <button type="button" class="btn btn-sm btn-outline-primary placeholder-btn" onclick="insertPlaceholder('{name_en}')">{name_en}</button>
      <button type="button" class="btn btn-sm btn-outline-primary placeholder-btn" onclick="insertPlaceholder('{username}')">{username}</button>
      <button type="button" class="btn btn-sm btn-outline-primary placeholder-btn" onclick="insertPlaceholder('{password}')">{password}</button>
      <button type="button" class="btn btn-sm btn-outline-primary placeholder-btn" onclick="insertPlaceholder('{code}')">{code}</button>
      <button type="button" class="btn btn-sm btn-outline-danger mr-1" onclick="document.getElementById('smsMessage').value=''; updateCharCount()">
        <i class="fas fa-eraser ml-1"></i>مسح
      </button>
    </div>
  </div>
  <small class="text-muted d-block mt-2">
    {name} = اسم الموظف بالعربي &nbsp;|&nbsp; {name_en} = اسم الموظف بالإنجليزي &nbsp;|&nbsp; {username} = اسم المستخدم &nbsp;|&nbsp; {password} = كلمة المرور &nbsp;|&nbsp; {code} = كود الموظف.
    اكتب رابط التطبيق مباشرة داخل النص.
  </small>
</div>

{{-- ══ بطاقة الفلاتر ══ --}}
<div class="filter-card">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="font-weight-bold mb-0" style="color:#1e3a5f">
      <i class="fas fa-filter ml-2"></i>البحث المتقدم عن الموظفين
    </h5>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleAdvFilters">
      <i class="fas fa-sliders-h ml-1"></i>فلاتر إضافية
    </button>
  </div>

  <div class="row" id="basicFilters">
    <div class="col-md-2 col-6 mb-2">
      <input type="text" id="f_name" class="form-control form-control-sm" placeholder="اسم الموظف">
    </div>
    <div class="col-md-2 col-6 mb-2">
      <input type="text" id="f_code" class="form-control form-control-sm" placeholder="كود الموظف">
    </div>
    <div class="col-md-2 col-6 mb-2">
      <select id="f_has_phone" class="form-control form-control-sm">
        <option value="">الهاتف — الكل</option>
        <option value="yes" selected>لديه رقم هاتف</option>
        <option value="no">بدون رقم هاتف</option>
      </select>
    </div>
    <div class="col-md-2 col-6 mb-2">
      <select id="f_has_credentials" class="form-control form-control-sm">
        <option value="">بيانات الدخول — الكل</option>
        <option value="yes" selected>لديه بيانات دخول</option>
        <option value="no">بدون بيانات دخول</option>
      </select>
    </div>
    <div class="col-md-2 mb-2">
      <button type="button" class="btn btn-primary btn-sm btn-block" onclick="filterEmployees()">
        <i class="fas fa-search ml-1"></i>بحث وعرض
      </button>
    </div>
  </div>

  <div id="advFilters" style="display:none">
    <hr class="my-2">
    <div class="row">
      <div class="col-md-2 col-6 mb-2">
        <label class="small">القسم</label>
        <select id="f_dept" class="form-control form-control-sm">
          <option value="">الكل</option>
          @foreach($departments as $d)
            <option value="{{ $d->id }}">{{ $d->dep_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6 mb-2">
        <label class="small">الفرع</label>
        <select id="f_branch" class="form-control form-control-sm">
          <option value="">الكل</option>
          @foreach($branches as $br)
            <option value="{{ $br->id }}">{{ $br->branch_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6 mb-2">
        <label class="small">الوظيفة</label>
        <select id="f_job" class="form-control form-control-sm">
          <option value="">الكل</option>
          @foreach($jobs_categories as $j)
            <option value="{{ $j->id }}">{{ $j->job_name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-6 mb-2">
        <label class="small">الشيفت</label>
        <select id="f_shift" class="form-control form-control-sm">
          <option value="">الكل</option>
          @foreach($shifts as $sh)
            <option value="{{ $sh->id }}">{{ $sh->type }}</option>
          @endforeach
        </select>
      </div>
      @if($clients->count())
      <div class="col-md-2 col-6 mb-2">
        <label class="small">العميل (Outsource)</label>
        <select id="f_client" class="form-control form-control-sm">
          <option value="">الكل</option>
          <option value="0">موظفو الشركة فقط</option>
          @foreach($clients as $cl)
            <option value="{{ $cl->id }}">{{ $cl->client_name }}</option>
          @endforeach
        </select>
      </div>
      @endif
    </div>
  </div>
</div>

{{-- ══ جدول الموظفين ══ --}}
<div class="card" id="employeesSection" style="display:none">
  <div class="card-header d-flex justify-content-between align-items-center py-2">
    <h6 class="mb-0 font-weight-bold" style="color:#1e3a5f">
      <i class="fas fa-users ml-1"></i>
      نتائج البحث: <span id="resultCount" class="badge badge-primary">0</span> موظف
      &nbsp;|&nbsp; لديهم بيانات دخول: <span id="withCredCount" class="badge badge-success">0</span>
    </h6>
    <div>
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
        <i class="fas fa-check-square ml-1"></i>تحديد الكل
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary mr-1" onclick="deselectAll()">
        <i class="fas fa-square ml-1"></i>إلغاء التحديد
      </button>
      <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="selectWithCredentials()">
        <i class="fas fa-key ml-1"></i>تحديد أصحاب بيانات الدخول
      </button>
    </div>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive" style="max-height:380px;overflow-y:auto">
      <table class="table table-bordered table-hover emp-table mb-0">
        <thead>
          <tr>
            <th width="40"><input type="checkbox" id="masterCheck" onchange="toggleAll(this)"></th>
            <th>م</th>
            <th>اسم الموظف</th>
            <th>الكود</th>
            <th>رقم الهاتف</th>
            <th>بيانات الدخول</th>
          </tr>
        </thead>
        <tbody id="empTableBody">
          <tr><td colspan="6" class="text-center text-muted py-3">اضغط "بحث وعرض" لاستعراض الموظفين</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
      <small class="text-muted">
        المحدد: <strong id="selectedCount">0</strong> موظف
        &nbsp;|&nbsp; المحدد ببيانات دخول: <strong id="selectedWithCred">0</strong>
      </small>
      <div class="d-flex align-items-center">
        <label class="small mb-0 ml-2">الفاصل بين كل رسالة (ثانية):</label>
        <input type="number" id="sendDelay" class="form-control form-control-sm" style="width:80px" value="75" min="30" max="600">
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center">
      <div id="progressWrap" style="display:none;flex:1;margin-left:1rem">
        <div class="progress" style="height:20px">
          <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width:0%">0%</div>
        </div>
        <small class="text-muted" id="progressLabel"></small>
      </div>
      <div>
        <button type="button" class="btn btn-danger mr-1" id="stopBtn" style="display:none" onclick="stopSending()">
          <i class="fas fa-stop ml-1"></i>إيقاف
        </button>
        <button type="button" class="btn btn-success" id="sendBtn" onclick="startSending()" disabled>
          <i class="fas fa-paper-plane ml-2"></i>إرسال بيانات الدخول
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ══ نتائج الإرسال ══ --}}
<div class="results-card" id="resultsSection" style="display:none">
  <h5 class="font-weight-bold mb-3" style="color:#856404">
    <i class="fas fa-clipboard-list ml-2"></i>نتائج الإرسال
  </h5>

  <div class="row mb-3" id="summaryBoxes"></div>

  <div class="table-responsive">
    <table class="table table-bordered table-sm emp-table">
      <thead>
        <tr>
          <th>#</th>
          <th>اسم الموظف</th>
          <th>الكود</th>
          <th>رقم الهاتف</th>
          <th>الحالة</th>
          <th>الملاحظة</th>
        </tr>
      </thead>
      <tbody id="resultsBody"></tbody>
    </table>
  </div>
</div>

</div>
@endsection

@section('script')
<script>
const FILTER_URL    = '{{ route("sms.filter") }}';
const SEND_ONE_URL  = '{{ route("sms.credentials.send") }}';
const CSRF          = '{{ csrf_token() }}';

let sending        = false;
let stopRequested  = false;
let sentCount = 0, failedCount = 0, skippedCount = 0;

function updateCharCount() {
  const len = document.getElementById('smsMessage').value.length;
  document.getElementById('charCount').textContent = len + ' / 800 حرف';
}

function insertPlaceholder(tag) {
  const ta = document.getElementById('smsMessage');
  const start = ta.selectionStart ?? ta.value.length;
  const end   = ta.selectionEnd ?? ta.value.length;
  ta.value = ta.value.slice(0, start) + tag + ta.value.slice(end);
  ta.focus();
  ta.selectionStart = ta.selectionEnd = start + tag.length;
  updateCharCount();
}

document.getElementById('toggleAdvFilters').addEventListener('click', function() {
  const el = document.getElementById('advFilters');
  el.style.display = el.style.display === 'none' ? '' : 'none';
  this.querySelector('i').className = el.style.display === 'none'
    ? 'fas fa-sliders-h ml-1' : 'fas fa-chevron-up ml-1';
});

function filterEmployees() {
  const params = {
    search_name:       document.getElementById('f_name').value,
    search_code:       document.getElementById('f_code').value,
    has_phone:         document.getElementById('f_has_phone').value,
    has_credentials:   document.getElementById('f_has_credentials').value,
    search_dept:       document.getElementById('f_dept').value,
    search_branch:     document.getElementById('f_branch').value,
    search_job:        document.getElementById('f_job').value,
    search_shift:      document.getElementById('f_shift').value,
    client_id:         (document.getElementById('f_client') || {value:''}).value,
  };

  fetch(FILTER_URL + '?' + new URLSearchParams(params), {
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => renderEmployeeTable(data))
  .catch(() => alert('حدث خطأ أثناء البحث'));
}

function renderEmployeeTable(data) {
  document.getElementById('resultCount').textContent  = data.count;
  document.getElementById('withCredCount').textContent = data.employees.filter(e => e.hasCredentials).length;
  document.getElementById('employeesSection').style.display = '';
  document.getElementById('masterCheck').checked = false;

  const tbody = document.getElementById('empTableBody');
  if (!data.employees.length) {
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">لا يوجد موظفون بهذه المعايير</td></tr>';
    updateSelectedCount();
    return;
  }

  tbody.innerHTML = data.employees.map((e, i) => {
    const hasPhone = e.phone !== '';
    const phoneTd  = hasPhone
      ? `<span class="text-success">${e.phone}</span>`
      : '<span class="text-danger"><i class="fas fa-times-circle"></i> لا يوجد</span>';
    const credTd = e.hasCredentials
      ? '<span class="badge badge-success">نعم</span>'
      : '<span class="badge badge-nocred">لا</span>';

    return `<tr data-id="${e.id}" data-name="${escapeHtml(e.name)}" data-code="${escapeHtml(e.code)}" data-phone="${escapeHtml(e.phone)}">
      <td class="text-center">
        <input type="checkbox" class="emp-check" data-id="${e.id}" data-has-phone="${hasPhone ? '1' : '0'}" data-has-cred="${e.hasCredentials ? '1' : '0'}" onchange="updateSelectedCount()">
      </td>
      <td>${i + 1}</td>
      <td class="font-weight-bold">${escapeHtml(e.name)}</td>
      <td><small>${escapeHtml(e.code)}</small></td>
      <td>${phoneTd}</td>
      <td>${credTd}</td>
    </tr>`;
  }).join('');

  updateSelectedCount();
}

function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function toggleAll(cb) {
  document.querySelectorAll('.emp-check').forEach(c => { c.checked = cb.checked; });
  updateSelectedCount();
}
function selectAll()               { document.querySelectorAll('.emp-check').forEach(c => c.checked = true);  updateSelectedCount(); document.getElementById('masterCheck').checked = true; }
function deselectAll()             { document.querySelectorAll('.emp-check').forEach(c => c.checked = false); updateSelectedCount(); document.getElementById('masterCheck').checked = false; }
function selectWithCredentials()   { document.querySelectorAll('.emp-check').forEach(c => { c.checked = c.dataset.hasCred === '1' && c.dataset.hasPhone === '1'; }); updateSelectedCount(); }

function updateSelectedCount() {
  const checks    = document.querySelectorAll('.emp-check:checked');
  const withCred  = [...checks].filter(c => c.dataset.hasCred === '1').length;
  document.getElementById('selectedCount').textContent     = checks.length;
  document.getElementById('selectedWithCred').textContent  = withCred;
  document.getElementById('sendBtn').disabled = checks.length === 0 || sending;
}

function sleep(ms) { return new Promise(res => setTimeout(res, ms)); }

// الرسالة الواحدة ممكن تاخد أكتر من ساعة بالفاصل الزمني بين الرسائل — الـ CSRF token
// المأخوذ عند تحميل الصفحة يبقى قديم (Laravel session قد ينتهي/يتجدد أثناء الإرسال)،
// فكل استدعاء لاحق يفشل بـ 419 CSRF mismatch للأبد لحد ما تعمل رفرش للصفحة.
// الحل: نقرأ التوكن الحالي من كوكي XSRF-TOKEN قبل كل طلب — Laravel يجدد هذه الكوكي
// تلقائياً مع كل response فتبقى دايماً صالحة حتى لو الـ session تجدد.
function getFreshCsrfToken() {
  const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
  return m ? decodeURIComponent(m[1]) : CSRF;
}

async function postSendOne(payload) {
  const r = await fetch(SEND_ONE_URL, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getFreshCsrfToken(), 'Accept': 'application/json' },
    body:    JSON.stringify(payload),
  });
  if (r.status === 419) {
    // توكن قديم بشكل استثنائي (سباق توقيت) — أعد المحاولة مرة واحدة بتوكن محدّث
    await sleep(500);
    return fetch(SEND_ONE_URL, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-XSRF-TOKEN': getFreshCsrfToken(), 'Accept': 'application/json' },
      body:    JSON.stringify(payload),
    });
  }
  return r;
}

// حالات لا تستحق إعادة محاولة (مش هتنجح لو اتكررت): تم الإرسال، أو الموظف مالوش هاتف/بيانات دخول أصلاً
const NON_RETRYABLE_STATUSES = ['sent', 'no_phone', 'no_credentials', 'not_found'];

async function startSending(skipIntroConfirm = false) {
  const template = document.getElementById('smsMessage').value.trim();
  if (!template) { alert('يُرجى كتابة نص الرسالة أولاً'); return; }
  if (!template.includes('{username}') || !template.includes('{password}')) {
    if (!confirm('الرسالة لا تحتوي على {username} أو {password} — هل تريد المتابعة بدون بيانات الدخول؟')) return;
  }

  const checks = [...document.querySelectorAll('.emp-check:checked')];
  if (!checks.length) { alert('يُرجى تحديد موظف واحد على الأقل'); return; }

  const delaySec = Math.max(30, parseInt(document.getElementById('sendDelay').value || '75', 10));
  const targets = checks.map(c => {
    const tr = c.closest('tr');
    return { id: c.dataset.id, name: tr.dataset.name, code: tr.dataset.code, phone: tr.dataset.phone };
  });

  if (!skipIntroConfirm) {
    const estMin = Math.ceil((targets.length - 1) * delaySec / 60);
    const confirmMsg = `سيتم إرسال ${targets.length} رسالة، برسالة كل ${delaySec} ثانية (~${estMin} دقيقة تقريباً).\n` +
                        `يُرجى إبقاء هذه الصفحة مفتوحة حتى الانتهاء.\n\nهل تريد المتابعة؟`;
    if (!confirm(confirmMsg)) return;
  }

  sending = true; stopRequested = false;
  sentCount = 0; failedCount = 0; skippedCount = 0;
  document.getElementById('sendBtn').disabled = true;
  document.getElementById('stopBtn').style.display = '';
  document.getElementById('progressWrap').style.display = '';
  document.getElementById('resultsSection').style.display = '';
  document.getElementById('resultsBody').innerHTML = '';
  updateSummaryBoxes();

  const statusById = {};

  for (let i = 0; i < targets.length; i++) {
    if (stopRequested) break;
    const emp = targets[i];
    updateProgress(i, targets.length, emp.name);

    let data;
    try {
      const r = await postSendOne({ employee_id: emp.id, template });
      data = await r.json();
    } catch (e) {
      data = { status: 'failed', message: 'خطأ في الاتصال' };
    }

    statusById[emp.id] = data.status || 'failed';
    appendResultRow(i + 1, emp, data);

    if (i < targets.length - 1 && !stopRequested) {
      await sleep(delaySec * 1000);
    }
  }

  sending = false;
  document.getElementById('stopBtn').style.display = 'none';
  updateProgress(targets.length, targets.length, stopRequested ? 'تم الإيقاف' : 'اكتمل الإرسال');

  // بعد الانتهاء (أو الإيقاف): نلغي تحديد اللي اتبعتله بنجاح أو اللي مش هيفيد إعادة
  // المحاولة معاه، ونسيب محدد بس اللي فشل و اللي لسه ما اتبعتلوش (وقف قبل ما يوصله)
  targets.forEach(emp => {
    const status = statusById[emp.id];
    if (status && NON_RETRYABLE_STATUSES.includes(status)) {
      const cb = document.querySelector(`.emp-check[data-id="${emp.id}"]`);
      if (cb) cb.checked = false;
    }
  });
  updateSelectedCount();

  const remaining = document.querySelectorAll('.emp-check:checked').length;
  if (remaining > 0) {
    const msg = failedCount > 0
      ? `فشل إرسال ${failedCount} رسالة${stopRequested ? ' (وتم إيقاف الباقي)' : ''}. باقي ${remaining} موظف محدد.\nهل تريد إعادة المحاولة الآن؟`
      : `تم إيقاف الإرسال قبل الوصول لـ ${remaining} موظف. هل تريد استكمال الإرسال لهم الآن؟`;
    if (confirm(msg)) {
      startSending(true);
    }
  }
}

function stopSending() { stopRequested = true; }

function updateProgress(done, total, label) {
  const pct = total ? Math.round((done / total) * 100) : 0;
  const bar = document.getElementById('progressBar');
  bar.style.width = pct + '%';
  bar.textContent = pct + '%';
  document.getElementById('progressLabel').textContent = `(${done} / ${total}) — ${label}`;
}

function appendResultRow(idx, emp, data) {
  const status = data.status || 'failed';
  if (status === 'sent') sentCount++;
  else if (status === 'failed' || status === 'disabled' || status === 'not_found') failedCount++;
  else skippedCount++;

  const badgeCls = status === 'sent' ? 'badge-sent' : (status === 'no_phone' || status === 'no_credentials' ? 'badge-nophone' : 'badge-failed');
  const icon = status === 'sent'
    ? '<i class="fas fa-check-circle text-success"></i>'
    : (status === 'no_phone' || status === 'no_credentials'
        ? '<i class="fas fa-minus-circle text-secondary"></i>'
        : '<i class="fas fa-times-circle text-danger"></i>');
  const label = {
    sent: 'تم الإرسال', failed: 'فشل الإرسال', disabled: 'الخدمة معطّلة',
    not_found: 'الموظف غير موجود', no_phone: 'بدون هاتف', no_credentials: 'بدون بيانات دخول',
  }[status] || status;

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${idx}</td>
    <td class="font-weight-bold">${escapeHtml(emp.name)}</td>
    <td><small>${escapeHtml(emp.code)}</small></td>
    <td>${escapeHtml(emp.phone)}</td>
    <td>${icon} <span class="badge ${badgeCls}">${label}</span></td>
    <td><small class="text-muted">${escapeHtml(data.message || '')}</small></td>`;
  document.getElementById('resultsBody').appendChild(tr);

  updateSummaryBoxes();
}

function updateSummaryBoxes() {
  document.getElementById('summaryBoxes').innerHTML = `
    <div class="col-md-4 col-6 mb-2">
      <div class="stat-box" style="background:#d4edda;border:1px solid #c3e6cb">
        <div style="font-size:2rem;font-weight:800;color:#155724">${sentCount}</div>
        <div class="text-success font-weight-bold">تم الإرسال</div>
      </div>
    </div>
    <div class="col-md-4 col-6 mb-2">
      <div class="stat-box" style="background:#f8d7da;border:1px solid #f5c6cb">
        <div style="font-size:2rem;font-weight:800;color:#721c24">${failedCount}</div>
        <div class="text-danger font-weight-bold">فشل</div>
      </div>
    </div>
    <div class="col-md-4 col-6 mb-2">
      <div class="stat-box" style="background:#e2e3e5;border:1px solid #d6d8db">
        <div style="font-size:2rem;font-weight:800;color:#383d41">${skippedCount}</div>
        <div class="text-secondary font-weight-bold">تم التخطي</div>
      </div>
    </div>`;
}

document.addEventListener('DOMContentLoaded', function() {
  updateCharCount();
  filterEmployees();
});
</script>
@endsection
