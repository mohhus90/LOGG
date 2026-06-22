@extends('admin.layouts.admin')

@section('title'){{ __('admin.emp_view_data') }}@endsection
@section('start'){{ __('admin.hr_management') }}@endsection
@section('home')<a href="{{ route('employees.index') }}">{{ __('admin.emp_title') }}</a>@endsection
@section('startpage'){{ __('admin.view') }}@endsection

@section('css')
<style>
/* ── Profile Header ── */
.emp-profile-header {
    background: linear-gradient(135deg, #1a237e 0%, #283593 40%, #3949ab 100%);
    border-radius: 12px 12px 0 0;
    padding: 32px 28px 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.emp-profile-header::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: rgba(255,255,255,0.06);
}
.emp-photo-wrap {
    width: 100px; height: 100px;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.4);
    overflow: hidden;
    background: #fff;
    flex-shrink: 0;
}
.emp-photo-wrap img { width: 100%; height: 100%; object-fit: cover; }
.emp-header-name { font-size: 1.45rem; font-weight: 700; margin-bottom: 4px; }
.emp-header-sub  { font-size: 0.9rem; opacity: .8; }
.emp-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    margin: 2px;
}
.badge-working   { background: rgba(72,199,142,0.25); border: 1px solid #48c78e; color: #d1fae5; }
.badge-resigned  { background: rgba(239,68,68,0.25);  border: 1px solid #ef4444; color: #fee2e2; }
.badge-client    { background: rgba(251,191,36,0.2);  border: 1px solid #f59e0b; color: #fef3c7; }
.badge-male      { background: rgba(96,165,250,0.2);  border: 1px solid #60a5fa; color: #dbeafe; }
.badge-female    { background: rgba(236,72,153,0.2);  border: 1px solid #ec4899; color: #fce7f3; }

/* ── Section Cards ── */
.info-section { background: #fff; border-radius: 0; border-bottom: 1px solid #e9ecef; }
.info-section:last-child { border-bottom: none; border-radius: 0 0 12px 12px; }
.section-header {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 24px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    font-weight: 700;
    font-size: .93rem;
    color: #374151;
    cursor: pointer;
    user-select: none;
}
.section-header .sec-icon {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; color: #fff; flex-shrink: 0;
}
.section-body { padding: 20px 24px; }

/* ── Info Grid ── */
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; }
.info-item label {
    display: block; font-size: .72rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px;
    color: #9ca3af; margin-bottom: 4px;
}
.info-item .info-val {
    font-size: .92rem; color: #111827; font-weight: 500;
    padding: 7px 12px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    min-height: 36px;
    word-break: break-word;
}
.info-item .info-val.empty { color: #9ca3af; font-style: italic; }
.info-item .info-val.highlighted { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }

/* ── Document Cards ── */
.doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
.doc-card {
    border: 2px dashed #e5e7eb;
    border-radius: 12px;
    padding: 18px 14px;
    text-align: center;
    transition: all .2s;
    position: relative;
}
.doc-card.has-file { border-style: solid; border-color: #3b82f6; background: #eff6ff; }
.doc-card .doc-icon {
    font-size: 2rem; margin-bottom: 8px;
    color: #9ca3af;
}
.doc-card.has-file .doc-icon { color: #3b82f6; }
.doc-card .doc-name { font-size: .82rem; font-weight: 600; color: #374151; margin-bottom: 4px; }
.doc-card .doc-filename { font-size: .73rem; color: #6b7280; margin-bottom: 10px; word-break: break-all; }
.doc-card .doc-actions { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; }
.doc-upload-btn { font-size: .8rem; }
.doc-badge-uploaded {
    position: absolute; top: 8px; right: 8px;
    background: #10b981; color: #fff;
    font-size: .65rem; padding: 2px 7px;
    border-radius: 20px; font-weight: 700;
}

/* ── Actions Bar ── */
.actions-bar {
    display: flex; gap: 10px; flex-wrap: wrap;
    padding: 16px 24px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    border-radius: 0 0 12px 12px;
}

/* ── Tabs ── */
.emp-tabs { border-bottom: 2px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 4px; padding: 0 24px; background: #fff; }
.emp-tab {
    padding: 10px 18px; font-size: .87rem; font-weight: 600;
    color: #6b7280; border: none; background: none;
    border-bottom: 3px solid transparent; margin-bottom: -2px;
    cursor: pointer; transition: all .15s;
}
.emp-tab.active { color: #3b82f6; border-bottom-color: #3b82f6; }
.emp-tab-pane { display: none; }
.emp-tab-pane.active { display: block; }

.card-outer {
    border-radius: 12px;
    box-shadow: 0 1px 8px rgba(0,0,0,.08);
    overflow: hidden;
    border: 1px solid #e5e7eb;
}
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- ── Profile Card ── --}}
  <div class="card-outer mb-4">

    {{-- Header --}}
    <div class="emp-profile-header">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <div class="emp-photo-wrap">
          @php $photoDoc = $documents->get('photo'); @endphp
          @if($photoDoc)
            <img src="{{ asset($photoDoc->doc_path) }}" alt="">
          @elseif(!empty($data['emp_photo']))
            <img src="{{ asset('assets/admin/uploads/' . $data['emp_photo']) }}" alt="">
          @elseif($data['emp_gender'] == 2)
            <img src="{{ asset('assets/admin/uploads/woman.png') }}" alt="">
          @else
            <img src="{{ asset('assets/admin/uploads/man.png') }}" alt="">
          @endif
        </div>
        <div class="flex-grow-1">
          <div class="emp-header-name">{{ $data['employee_name_A'] }}</div>
          <div class="emp-header-sub mb-2">{{ $data['employee_name_E'] }}</div>
          <div class="d-flex flex-wrap gap-1">
            <span class="emp-badge {{ $data['functional_status'] == 1 ? 'badge-working' : 'badge-resigned' }}">
              <i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-{{ app()->isLocale('ar') ? 'left' : 'right' }}:4px"></i>
              {{ $data['functional_status'] == 1 ? __('admin.emp_working') : __('admin.emp_not_working') }}
            </span>
            <span class="emp-badge {{ $data['emp_gender'] == 2 ? 'badge-female' : 'badge-male' }}">
              <i class="fas {{ $data['emp_gender'] == 2 ? 'fa-venus' : 'fa-mars' }}"></i>
              {{ $data['emp_gender'] == 2 ? __('admin.female') : __('admin.male') }}
            </span>
            @if($data['client_id'])
            <span class="emp-badge badge-client">
              <i class="fas fa-building"></i>
              {{ $data->client->client_name ?? '' }}
            </span>
            @endif
            @if($data['emp_jobs_id'])
            <span class="emp-badge" style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff">
              <i class="fas fa-briefcase"></i>
              {{ optional($data->jobs_categories)->job_name }}
            </span>
            @endif
          </div>
        </div>
        <div class="text-{{ app()->isLocale('ar') ? 'left' : 'right' }} ms-auto">
          <div style="font-size:1.6rem;font-weight:800;opacity:.95">#{{ $data['employee_id'] }}</div>
          @if($data['hrid'])
          <div style="font-size:.8rem;opacity:.7">كود العميل: {{ $data['hrid'] }}</div>
          @endif
          <div style="font-size:.75rem;opacity:.6;margin-top:4px">{{ __('admin.emp_join_date') }}: {{ $data['emp_start_date'] ?? '—' }}</div>
        </div>
      </div>
    </div>

    {{-- Tabs --}}
    <div class="emp-tabs" id="empTabs">
      <button class="emp-tab active" data-target="tab-basic"><i class="fas fa-user me-1"></i>{{ __('admin.emp_tab_basic') }}</button>
      <button class="emp-tab" data-target="tab-job"><i class="fas fa-briefcase me-1"></i>{{ __('admin.emp_tab_job') }}</button>
      <button class="emp-tab" data-target="tab-other"><i class="fas fa-info-circle me-1"></i>{{ __('admin.emp_tab_other') }}</button>
      <button class="emp-tab" data-target="tab-salary"><i class="fas fa-money-bill-wave me-1"></i>{{ __('admin.emp_tab_salary') }}</button>
      @if($data['client_id'])
      <button class="emp-tab" data-target="tab-client"><i class="fas fa-building me-1"></i>بيانات العميل</button>
      @endif
      <button class="emp-tab" data-target="tab-docs"><i class="fas fa-folder-open me-1"></i>ملفات التعيين</button>
    </div>

    {{-- ── TAB: Basic Data ── --}}
    <div class="emp-tab-pane active section-body" id="tab-basic">
      <div class="info-grid">
        <div class="info-item">
          <label>{{ __('admin.emp_code') }}</label>
          <div class="info-val highlighted">{{ $data['employee_id'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_finger_code') }}</label>
          <div class="info-val {{ empty($data['finger_id']) ? 'empty' : '' }}">{{ $data['finger_id'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_name_ar') }}</label>
          <div class="info-val">{{ $data['employee_name_A'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_name_en') }}</label>
          <div class="info-val">{{ $data['employee_name_E'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_national_id') }}</label>
          <div class="info-val {{ empty($data['national_id']) ? 'empty' : '' }}">{{ $data['national_id'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_insurance_no') }}</label>
          <div class="info-val {{ empty($data['insurance_no']) ? 'empty' : '' }}">{{ $data['insurance_no'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_mobile') }}</label>
          <div class="info-val {{ empty($data['emp_mobile']) ? 'empty' : '' }}">{{ $data['emp_mobile'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_home_phone') }}</label>
          <div class="info-val {{ empty($data['emp_home_tel']) ? 'empty' : '' }}">{{ $data['emp_home_tel'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>Email</label>
          <div class="info-val {{ empty($data['emp_email']) ? 'empty' : '' }}">{{ $data['emp_email'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_birth_date') }}</label>
          <div class="info-val {{ empty($data['birth_date']) ? 'empty' : '' }}">{{ $data['birth_date'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_gender') }}</label>
          <div class="info-val">
            @if($data['emp_gender'] == 1) {{ __('admin.male') }}
            @elseif($data['emp_gender'] == 2) {{ __('admin.female') }}
            @else —
            @endif
          </div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_marital_status') }}</label>
          <div class="info-val">
            @if($data['emp_social_status'] == 1) {{ __('admin.emp_single') }}
            @elseif($data['emp_social_status'] == 2) {{ __('admin.emp_married') }}
            @elseif($data['emp_social_status'] == 3) {{ __('admin.emp_married_dependent') }}
            @else —
            @endif
          </div>
        </div>
        <div class="info-item" style="grid-column: span 2">
          <label>{{ __('admin.emp_address') }}</label>
          <div class="info-val {{ empty($data['employee_address']) ? 'empty' : '' }}">{{ $data['employee_address'] ?? '—' }}</div>
        </div>
      </div>
    </div>

    {{-- ── TAB: Job Data ── --}}
    <div class="emp-tab-pane section-body" id="tab-job">
      <div class="info-grid">
        <div class="info-item">
          <label>{{ __('admin.emp_join_date') }}</label>
          <div class="info-val {{ empty($data['emp_start_date']) ? 'empty' : 'highlighted' }}">{{ $data['emp_start_date'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_status') }}</label>
          <div class="info-val">
            @if($data['functional_status'] == 1) <span style="color:#059669;font-weight:700">● {{ __('admin.emp_working') }}</span>
            @elseif($data['functional_status'] == 2) <span style="color:#dc2626;font-weight:700">● {{ __('admin.emp_not_working') }}</span>
            @else —
            @endif
          </div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_insurance_status') }}</label>
          <div class="info-val">
            @php $ins = [1=>__('admin.emp_insured'),2=>__('admin.emp_not_insured'),3=>__('admin.emp_training'),4=>__('admin.emp_service_ended')]; @endphp
            {{ $ins[$data['insurance_status']] ?? '—' }}
          </div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_job') }}</label>
          <div class="info-val">{{ optional($data->jobs_categories)->job_name ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_dept') }}</label>
          <div class="info-val">{{ optional($data->department)->dep_name ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_shift') }}</label>
          <div class="info-val">{{ optional($data->shifts_type)->type ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_branch') }}</label>
          <div class="info-val">{{ optional($data->branches)->branch_name ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_work_hours') }}</label>
          <div class="info-val {{ empty($data['daily_work_hours']) ? 'empty' : '' }}">{{ $data['daily_work_hours'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_leave_reason_label') }}</label>
          <div class="info-val">
            @php $res = [1=>__('admin.emp_resignation'),2=>__('admin.emp_fired'),3=>__('admin.emp_left_work'),4=>__('admin.emp_retirement_age'),5=>__('admin.emp_death')]; @endphp
            {{ $res[$data['resignation_status']] ?? '—' }}
          </div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_leave_date') }}</label>
          <div class="info-val {{ empty($data['resignation_date']) ? 'empty' : '' }}">{{ $data['resignation_date'] ?? '—' }}</div>
        </div>
        <div class="info-item" style="grid-column:span 2">
          <label>{{ __('admin.emp_leave_reason') }}</label>
          <div class="info-val {{ empty($data['resignation_cause']) ? 'empty' : '' }}">{{ $data['resignation_cause'] ?? '—' }}</div>
        </div>
      </div>
    </div>

    {{-- ── TAB: Other Data ── --}}
    <div class="emp-tab-pane section-body" id="tab-other">
      <div class="info-grid">
        <div class="info-item">
          <label>{{ __('admin.emp_military') }}</label>
          <div class="info-val">
            @php $mil=[1=>__('admin.emp_military_served'),2=>__('admin.emp_military_exempt'),3=>__('admin.emp_military_deferred'),4=>__('admin.emp_military_temp_exempt'),5=>__('admin.emp_military_not_required')]; @endphp
            {{ $mil[$data['emp_military_status']] ?? '—' }}
          </div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_education') }}</label>
          <div class="info-val {{ empty($data['emp_qualification']) ? 'empty' : '' }}">{{ $data['emp_qualification'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_edu_year') }}</label>
          <div class="info-val {{ empty($data['qualification_year']) ? 'empty' : '' }}">{{ $data['qualification_year'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_edu_grade') }}</label>
          <div class="info-val">
            @php $gr=[1=>__('admin.emp_distinction'),2=>__('admin.emp_very_good'),3=>__('admin.emp_very_good_high'),4=>__('admin.emp_good'),5=>__('admin.emp_accepted')]; @endphp
            {{ $gr[$data['qualification_grade']] ?? '—' }}
          </div>
        </div>
      </div>
    </div>

    {{-- ── TAB: Salary ── --}}
    <div class="emp-tab-pane section-body" id="tab-salary">
      <div class="info-grid">
        <div class="info-item">
          <label>{{ __('admin.emp_basic_salary') }}</label>
          <div class="info-val highlighted">{{ number_format($data['emp_sal'] ?? 0, 2) }} {{ __('admin.egp') }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_insurance_salary') }}</label>
          <div class="info-val">{{ number_format($data['emp_sal_insurance'] ?? 0, 2) }} {{ __('admin.egp') }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_fixed_allowance') }}</label>
          <div class="info-val">{{ number_format($data['emp_fixed_allowances'] ?? 0, 2) }} {{ __('admin.egp') }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_incentive') }}</label>
          <div class="info-val">{{ number_format($data['mtivation'] ?? 0, 2) }} {{ __('admin.egp') }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_health_insurance') }}</label>
          <div class="info-val">{{ number_format($data['medical_insurance'] ?? 0, 2) }} {{ __('admin.egp') }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_payment_method') }}</label>
          <div class="info-val">
            @if($data['sal_cash_visa'] == 1) {{ __('admin.emp_cash') }}
            @elseif($data['sal_cash_visa'] == 2) {{ __('admin.emp_visa') }}
            @else — @endif
          </div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_bank_name') }}</label>
          <div class="info-val {{ empty($data['bank_name']) ? 'empty' : '' }}">{{ $data['bank_name'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>{{ __('admin.emp_bank_account') }}</label>
          <div class="info-val {{ empty($data['bank_account']) ? 'empty' : '' }}">{{ $data['bank_account'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>Bank ID</label>
          <div class="info-val {{ empty($data['bank_ID']) ? 'empty' : '' }}">{{ $data['bank_ID'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>Bank Branch</label>
          <div class="info-val {{ empty($data['bank_branch']) ? 'empty' : '' }}">{{ $data['bank_branch'] ?? '—' }}</div>
        </div>
      </div>
    </div>

    {{-- ── TAB: Client Data ── --}}
    @if($data['client_id'])
    <div class="emp-tab-pane section-body" id="tab-client">
      <div class="info-grid">
        <div class="info-item">
          <label>العميل</label>
          <div class="info-val highlighted">{{ optional($data->client)->client_name ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>كود العميل (Custom ID)</label>
          <div class="info-val highlighted">{{ $data['hrid'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>جهة الاتصال الطارئة (Reference)</label>
          <div class="info-val {{ empty($data['reference_mobile']) ? 'empty' : '' }}">{{ $data['reference_mobile'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>صلة القرابة</label>
          <div class="info-val {{ empty($data['relative_relation']) ? 'empty' : '' }}">{{ $data['relative_relation'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>حالة أوراق التعيين</label>
          <div class="info-val {{ empty($data['hiring_documents_status']) ? 'empty' : '' }}">{{ $data['hiring_documents_status'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>تاريخ بداية التأمين الاجتماعي</label>
          <div class="info-val {{ empty($data['insurance_start_date']) ? 'empty' : '' }}">{{ $data['insurance_start_date'] ?? '—' }}</div>
        </div>
        <div class="info-item">
          <label>تاريخ انتهاء التأمين الاجتماعي</label>
          <div class="info-val {{ empty($data['insurance_end_date']) ? 'empty' : '' }}">{{ $data['insurance_end_date'] ?? '—' }}</div>
        </div>
        <div class="info-item" style="grid-column:span 2">
          <label>ملاحظات نموذج 1 (Form 1)</label>
          <div class="info-val {{ empty($data['form1_notes']) ? 'empty' : '' }}">{{ $data['form1_notes'] ?? '—' }}</div>
        </div>
        <div class="info-item" style="grid-column:span 2">
          <label>ملاحظات نموذج 6 (Form 6)</label>
          <div class="info-val {{ empty($data['form6_notes']) ? 'empty' : '' }}">{{ $data['form6_notes'] ?? '—' }}</div>
        </div>
        <div class="info-item" style="grid-column:span 3">
          <label>ملاحظات</label>
          <div class="info-val {{ empty($data['client_notes']) ? 'empty' : '' }}">{{ $data['client_notes'] ?? '—' }}</div>
        </div>
      </div>
    </div>
    @endif

    {{-- ── TAB: Documents ── --}}
    <div class="emp-tab-pane section-body" id="tab-docs">
      <div class="doc-grid">
        @foreach($docTypes as $type => $info)
          @php $doc = $documents->get($type); @endphp
          <div class="doc-card {{ $doc ? 'has-file' : '' }}">
            @if($doc)
              <span class="doc-badge-uploaded">✓ مرفوع</span>
            @endif
            <div class="doc-icon"><i class="fas {{ $info['icon'] }}"></i></div>
            <div class="doc-name">{{ $info['ar'] }}</div>
            @if($doc)
              <div class="doc-filename">{{ Str::limit($doc->doc_original_name, 30) }}</div>
              <div class="doc-actions">
                <a href="{{ route('employees.document.download', [$data->id, $doc->id]) }}"
                   class="btn btn-sm btn-primary doc-upload-btn">
                  <i class="fas fa-download"></i> تنزيل
                </a>
                <a href="{{ route('employees.document.delete', [$data->id, $doc->id]) }}"
                   class="btn btn-sm btn-outline-danger doc-upload-btn"
                   onclick="return confirm('حذف هذا الملف؟')">
                  <i class="fas fa-trash"></i>
                </a>
              </div>
            @else
              <div class="doc-filename" style="color:#9ca3af">لم يُرفع بعد</div>
            @endif

            {{-- Upload form --}}
            <form action="{{ route('employees.document.upload', $data->id) }}" method="POST"
                  enctype="multipart/form-data" class="mt-2">
              @csrf
              <input type="hidden" name="doc_type" value="{{ $type }}">
              <label class="btn btn-sm {{ $doc ? 'btn-outline-secondary' : 'btn-outline-primary' }} doc-upload-btn w-100">
                <i class="fas fa-upload"></i> {{ $doc ? 'استبدال' : 'رفع ملف' }}
                <input type="file" name="doc_file" class="d-none"
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       onchange="this.closest('form').submit()">
              </label>
            </form>
          </div>
        @endforeach
      </div>
      <div class="mt-3 p-3 rounded" style="background:#f0f9ff;border:1px solid #bae6fd;font-size:.82rem;color:#0369a1">
        <i class="fas fa-info-circle me-1"></i>
        الملفات المقبولة: PDF، صور (JPG/PNG)، مستندات Word — الحجم الأقصى 10MB لكل ملف
      </div>
    </div>

    {{-- ── Actions Bar ── --}}
    <div class="actions-bar">
      <a href="{{ route('employees.edit', $data->id) }}" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i> {{ __('admin.emp_edit') }}
      </a>
      <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-1"></i> {{ __('admin.back') }}
      </a>
    </div>

  </div>{{-- card-outer --}}

</div>
@endsection

@section('script')
<script>
document.querySelectorAll('.emp-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.emp-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.emp-tab-pane').forEach(p => p.classList.remove('active'));
        this.classList.add('active');
        var target = document.getElementById(this.dataset.target);
        if (target) target.classList.add('active');
    });
});
</script>
@endsection
