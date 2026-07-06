@extends('admin.layouts.admin')
@section('title') إرسال رسائل SMS @endsection
@section('start') الرسائل القصيرة @endsection
@section('home') <a href="{{ route('sms.compose') }}">SMS</a> @endsection
@section('startpage') إرسال جماعي @endsection

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
#empTableBody tr:hover { background:#f8f9fa; }
.select-all-row { background:#e8f4fd !important; }
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

{{-- ══ بطاقة نص الرسالة ══ --}}
<div class="compose-card">
  <h5 class="font-weight-bold mb-3" style="color:#155724">
    <i class="fas fa-pen-alt ml-2"></i>كتابة الرسالة
  </h5>
  <div class="form-group mb-1">
    <textarea id="smsMessage" class="form-control" rows="4"
      placeholder="اكتب نص الرسالة هنا... (حروف عربية أو إنجليزية)"
      maxlength="800" oninput="updateCharCount()"></textarea>
  </div>
  <div class="d-flex justify-content-between align-items-center">
    <small class="char-count" id="charCount">0 / 800 حرف &nbsp;|&nbsp; 0 رسالة</small>
    <div>
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTemplate('مرحباً {الاسم}، نود إعلامكم بـ...')">
        <i class="fas fa-file-alt ml-1"></i>قالب سريع
      </button>
      <button type="button" class="btn btn-sm btn-outline-danger mr-1" onclick="document.getElementById('smsMessage').value=''; updateCharCount()">
        <i class="fas fa-eraser ml-1"></i>مسح
      </button>
    </div>
  </div>
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

  {{-- فلاتر أساسية --}}
  <div class="row" id="basicFilters">
    <div class="col-md-2 col-6 mb-2">
      <input type="text" id="f_name" class="form-control form-control-sm" placeholder="اسم الموظف">
    </div>
    <div class="col-md-2 col-6 mb-2">
      <input type="text" id="f_code" class="form-control form-control-sm" placeholder="كود الموظف">
    </div>
    <div class="col-md-2 col-6 mb-2">
      <input type="text" id="f_phone" class="form-control form-control-sm" placeholder="رقم الهاتف">
    </div>
    <div class="col-md-2 col-6 mb-2">
      <select id="f_status" class="form-control form-control-sm">
        <option value="">الحالة الوظيفية — الكل</option>
        <option value="1">نشط</option>
        <option value="2">غير نشط</option>
      </select>
    </div>
    <div class="col-md-2 col-6 mb-2">
      <select id="f_has_phone" class="form-control form-control-sm">
        <option value="">الهاتف — الكل</option>
        <option value="yes">لديه رقم هاتف</option>
        <option value="no">بدون رقم هاتف</option>
      </select>
    </div>
    <div class="col-md-2 mb-2">
      <button type="button" class="btn btn-primary btn-sm btn-block" onclick="filterEmployees()">
        <i class="fas fa-search ml-1"></i>بحث وعرض
      </button>
    </div>
  </div>

  {{-- فلاتر متقدمة --}}
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
      <div class="col-md-2 col-6 mb-2">
        <label class="small">النوع</label>
        <select id="f_gender" class="form-control form-control-sm">
          <option value="">الكل</option>
          <option value="1">ذكر</option>
          <option value="2">أنثى</option>
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
      &nbsp;|&nbsp; لديهم رقم: <span id="withPhoneCount" class="badge badge-success">0</span>
    </h6>
    <div>
      <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
        <i class="fas fa-check-square ml-1"></i>تحديد الكل
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary mr-1" onclick="deselectAll()">
        <i class="fas fa-square ml-1"></i>إلغاء التحديد
      </button>
      <button type="button" class="btn btn-sm btn-outline-success mr-1" onclick="selectWithPhone()">
        <i class="fas fa-phone ml-1"></i>تحديد أصحاب الأرقام
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
            <th>القسم</th>
            <th>الوظيفة</th>
            <th>الحالة</th>
          </tr>
        </thead>
        <tbody id="empTableBody">
          <tr><td colspan="8" class="text-center text-muted py-3">اضغط "بحث وعرض" لاستعراض الموظفين</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between align-items-center py-2">
    <small class="text-muted">
      المحدد: <strong id="selectedCount">0</strong> موظف
      &nbsp;|&nbsp; المحدد بأرقام: <strong id="selectedWithPhone">0</strong>
    </small>
    <button type="button" class="btn btn-success" id="sendBtn" onclick="sendSms()" disabled>
      <i class="fas fa-paper-plane ml-2"></i>إرسال SMS للمحددين
    </button>
  </div>
</div>

{{-- ══ نتائج الإرسال ══ --}}
<div class="results-card" id="resultsSection" style="display:none">
  <h5 class="font-weight-bold mb-3" style="color:#856404">
    <i class="fas fa-clipboard-list ml-2"></i>نتائج الإرسال
  </h5>

  {{-- ملخص --}}
  <div class="row mb-3" id="summaryBoxes"></div>

  {{-- جدول التفصيل --}}
  <div class="table-responsive">
    <table class="table table-bordered table-sm emp-table">
      <thead>
        <tr>
          <th>#</th>
          <th>اسم الموظف</th>
          <th>الكود</th>
          <th>رقم الهاتف</th>
          <th>الحالة</th>
          <th>السبب / الملاحظة</th>
        </tr>
      </thead>
      <tbody id="resultsBody"></tbody>
    </table>
  </div>

  <div class="mt-2">
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportResultsCsv()">
      <i class="fas fa-file-csv ml-1"></i>تصدير النتائج CSV
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary mr-1" onclick="document.getElementById('resultsSection').style.display='none'">
      <i class="fas fa-times ml-1"></i>إخفاء النتائج
    </button>
  </div>
</div>

{{-- loading overlay --}}
<div id="loadingOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center">
  <div class="text-center text-white">
    <i class="fas fa-spinner fa-spin fa-3x mb-2"></i><br>
    <span id="loadingMsg" style="font-size:1.2rem">جاري الإرسال...</span>
  </div>
</div>

</div>
@endsection

@section('script')
<script>
const FILTER_URL = '{{ route("sms.filter") }}';
const SEND_URL   = '{{ route("sms.send") }}';
const CSRF       = '{{ csrf_token() }}';

let allEmployees = [];

// ── حساب عدد الحروف والرسائل ───────────────────────
function updateCharCount() {
  const txt  = document.getElementById('smsMessage').value;
  const len  = txt.length;
  const msgs = len === 0 ? 0 : (len <= 160 ? 1 : Math.ceil(len / 153));
  document.getElementById('charCount').textContent =
    len + ' / 800 حرف | ' + msgs + ' رسالة SMS';
}

function insertTemplate(t) {
  document.getElementById('smsMessage').value = t;
  updateCharCount();
}

// ── فلاتر متقدمة toggle ─────────────────────────────
document.getElementById('toggleAdvFilters').addEventListener('click', function() {
  const el = document.getElementById('advFilters');
  el.style.display = el.style.display === 'none' ? '' : 'none';
  this.querySelector('i').className = el.style.display === 'none'
    ? 'fas fa-sliders-h ml-1' : 'fas fa-chevron-up ml-1';
});

// ── البحث عن الموظفين ───────────────────────────────
function filterEmployees() {
  const params = {
    search_name:       document.getElementById('f_name').value,
    search_code:       document.getElementById('f_code').value,
    search_phone:      document.getElementById('f_phone').value,
    search_func_status:document.getElementById('f_status').value,
    has_phone:         document.getElementById('f_has_phone').value,
    search_dept:       document.getElementById('f_dept').value,
    search_branch:     document.getElementById('f_branch').value,
    search_job:        document.getElementById('f_job').value,
    search_shift:      document.getElementById('f_shift').value,
    search_gender:     document.getElementById('f_gender').value,
    client_id:         (document.getElementById('f_client') || {value:''}).value,
  };

  showLoading('جاري البحث عن الموظفين...');

  fetch(FILTER_URL + '?' + new URLSearchParams(params), {
    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
  })
  .then(r => r.json())
  .then(data => {
    hideLoading();
    allEmployees = data.employees;
    renderEmployeeTable(data);
  })
  .catch(() => { hideLoading(); alert('حدث خطأ أثناء البحث'); });
}

function renderEmployeeTable(data) {
  document.getElementById('resultCount').textContent    = data.count;
  document.getElementById('withPhoneCount').textContent = data.withPhone;
  document.getElementById('employeesSection').style.display = '';
  document.getElementById('masterCheck').checked = false;

  const tbody = document.getElementById('empTableBody');
  if (!data.employees.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">لا يوجد موظفون بهذه المعايير</td></tr>';
    updateSelectedCount();
    return;
  }

  tbody.innerHTML = data.employees.map((e, i) => {
    const hasPhone  = e.phone !== '';
    const statusBadge = e.status == 1
      ? '<span class="badge badge-success">نشط</span>'
      : '<span class="badge badge-secondary">غير نشط</span>';
    const phoneTd = hasPhone
      ? `<span class="text-success">${e.phone}</span>`
      : '<span class="text-danger"><i class="fas fa-times-circle"></i> لا يوجد</span>';

    return `<tr data-id="${e.id}" data-has-phone="${hasPhone ? '1' : '0'}">
      <td class="text-center">
        <input type="checkbox" class="emp-check" data-id="${e.id}" data-has-phone="${hasPhone ? '1' : '0'}" onchange="updateSelectedCount()">
      </td>
      <td>${i + 1}</td>
      <td class="font-weight-bold">${e.name}</td>
      <td><small>${e.code}</small></td>
      <td>${phoneTd}</td>
      <td><small>${e.department}</small></td>
      <td><small>${e.job}</small></td>
      <td>${statusBadge}</td>
    </tr>`;
  }).join('');

  updateSelectedCount();
}

// ── تحديد / إلغاء تحديد ────────────────────────────
function toggleAll(cb) {
  document.querySelectorAll('.emp-check').forEach(c => { c.checked = cb.checked; });
  updateSelectedCount();
}
function selectAll()         { document.querySelectorAll('.emp-check').forEach(c => c.checked = true);  updateSelectedCount(); document.getElementById('masterCheck').checked = true; }
function deselectAll()       { document.querySelectorAll('.emp-check').forEach(c => c.checked = false); updateSelectedCount(); document.getElementById('masterCheck').checked = false; }
function selectWithPhone()   { document.querySelectorAll('.emp-check').forEach(c => { c.checked = c.dataset.hasPhone === '1'; }); updateSelectedCount(); }

function updateSelectedCount() {
  const checks     = document.querySelectorAll('.emp-check:checked');
  const withPhone  = [...checks].filter(c => c.dataset.hasPhone === '1').length;
  document.getElementById('selectedCount').textContent    = checks.length;
  document.getElementById('selectedWithPhone').textContent = withPhone;
  document.getElementById('sendBtn').disabled = checks.length === 0;
}

// ── إرسال SMS ───────────────────────────────────────
function sendSms() {
  const message = document.getElementById('smsMessage').value.trim();
  if (!message) { alert('يُرجى كتابة نص الرسالة أولاً'); return; }

  const ids = [...document.querySelectorAll('.emp-check:checked')].map(c => c.dataset.id);
  if (!ids.length) { alert('يُرجى تحديد موظف واحد على الأقل'); return; }

  const withPhone = [...document.querySelectorAll('.emp-check:checked')].filter(c => c.dataset.hasPhone === '1').length;
  const noPhone   = ids.length - withPhone;

  let confirmMsg = `سيتم إرسال SMS لـ ${ids.length} موظف (${withPhone} لديهم أرقام).`;
  if (noPhone > 0) confirmMsg += `\n⚠️ ${noPhone} موظف بدون رقم هاتف لن يصلهم SMS.`;
  confirmMsg += '\n\nهل تريد المتابعة؟';
  if (!confirm(confirmMsg)) return;

  showLoading('جاري إرسال الرسائل...');

  fetch(SEND_URL, {
    method:  'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    body:    JSON.stringify({ message, employee_ids: ids }),
  })
  .then(r => r.json())
  .then(data => {
    hideLoading();
    if (!data.success) { alert('خطأ: ' + data.message); return; }
    renderResults(data);
  })
  .catch(() => { hideLoading(); alert('حدث خطأ في الاتصال'); });
}

function renderResults(data) {
  const s = data.summary;

  // ملخص
  document.getElementById('summaryBoxes').innerHTML = `
    <div class="col-md-3 col-6 mb-2">
      <div class="stat-box" style="background:#d4edda;border:1px solid #c3e6cb">
        <div style="font-size:2rem;font-weight:800;color:#155724">${s.sent}</div>
        <div class="text-success font-weight-bold">تم الإرسال</div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
      <div class="stat-box" style="background:#f8d7da;border:1px solid #f5c6cb">
        <div style="font-size:2rem;font-weight:800;color:#721c24">${s.failed}</div>
        <div class="text-danger font-weight-bold">فشل الإرسال</div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
      <div class="stat-box" style="background:#e2e3e5;border:1px solid #d6d8db">
        <div style="font-size:2rem;font-weight:800;color:#383d41">${s.no_phone}</div>
        <div class="text-secondary font-weight-bold">بدون رقم هاتف</div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-2">
      <div class="stat-box" style="background:#cce5ff;border:1px solid #b8daff">
        <div style="font-size:2rem;font-weight:800;color:#004085">${s.total}</div>
        <div class="text-primary font-weight-bold">الإجمالي</div>
      </div>
    </div>`;

  // جدول التفصيل
  document.getElementById('resultsBody').innerHTML = data.results.map((r, i) => {
    const badgeCls = r.status === 'sent' ? 'badge-sent' : (r.status === 'failed' ? 'badge-failed' : 'badge-nophone');
    const icon     = r.status === 'sent'
      ? '<i class="fas fa-check-circle text-success"></i>'
      : (r.status === 'failed'
          ? '<i class="fas fa-times-circle text-danger"></i>'
          : '<i class="fas fa-phone-slash text-secondary"></i>');

    return `<tr>
      <td>${i + 1}</td>
      <td class="font-weight-bold">${r.name}</td>
      <td><small>${r.code}</small></td>
      <td>${r.phone}</td>
      <td>${icon} <span class="badge ${badgeCls}">${r.label}</span></td>
      <td><small class="text-muted">${
        r.status === 'sent'     ? 'تم الإرسال بنجاح' :
        r.status === 'failed'   ? 'تعذّر الإرسال — تحقق من بيانات الاتصال' :
                                  'لا يوجد رقم هاتف مسجّل لهذا الموظف'
      }</small></td>
    </tr>`;
  }).join('');

  document.getElementById('resultsSection').style.display = '';
  document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
}

// ── تصدير CSV ───────────────────────────────────────
function exportResultsCsv() {
  const rows = document.querySelectorAll('#resultsBody tr');
  if (!rows.length) return;

  let csv = '﻿#,الاسم,الكود,الهاتف,الحالة,الملاحظة\n';
  rows.forEach(row => {
    const cells = row.querySelectorAll('td');
    const line = [
      cells[0].innerText.trim(),
      cells[1].innerText.trim(),
      cells[2].innerText.trim(),
      cells[3].innerText.trim(),
      cells[4].innerText.trim(),
      cells[5].innerText.trim(),
    ].map(c => '"' + c.replace(/"/g, '""') + '"').join(',');
    csv += line + '\n';
  });

  const a   = document.createElement('a');
  a.href    = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
  a.download = 'sms_results_{{ now()->format("Y-m-d_His") }}.csv';
  a.click();
}

// ── helpers ─────────────────────────────────────────
function showLoading(msg) {
  document.getElementById('loadingMsg').textContent = msg || 'جاري التحميل...';
  document.getElementById('loadingOverlay').style.display = 'flex';
}
function hideLoading() {
  document.getElementById('loadingOverlay').style.display = 'none';
}

// تشغيل بحث افتراضي (الموظفين النشطين) عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('f_status').value = '1';
  document.getElementById('f_has_phone').value = 'yes';
  filterEmployees();
});
</script>
@endsection
