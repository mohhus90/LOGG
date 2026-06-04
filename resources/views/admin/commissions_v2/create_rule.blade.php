{{-- FILE: resources/views/admin/commissions_v2/create_rule.blade.php --}}
@extends('admin.layouts.admin')
@section('title') إضافة قاعدة عمولة @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions_v2.rules') }}">قواعد العمولات</a> @endsection
@section('startpage') إضافة قاعدة @endsection

@section('css')
<style>
.option-card { cursor:pointer; border:2px solid #dee2e6; border-radius:8px; padding:12px 10px; transition:.2s; text-align:center; }
.option-card:hover, .option-card.selected { border-color:#007bff; background:#f0f7ff; }
.option-card .icon { font-size:1.6em; margin-bottom:4px; }
.option-card .label { font-size:.82em; font-weight:600; color:#333; }
.tier-row { background:#f8f9fa; border-radius:6px; padding:8px 12px; margin-bottom:6px; }
</style>
@endsection

@section('content')
<div class="col-md-10 mx-auto">
<div class="card card-success">
  <div class="card-header">
    <h3 class="card-title"><i class="fas fa-plus-circle ml-2"></i>إضافة قاعدة عمولة جديدة</h3>
  </div>

  <form action="{{ route('commissions_v2.store_rule') }}" method="POST" id="ruleForm">
    @csrf
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      {{-- الاسم والكود --}}
      <div class="row">
        <div class="col-md-7 form-group">
          <label>اسم القاعدة <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" required id="ruleName"
            value="{{ old('name') }}"
            placeholder="مثال: عمولة مبيعات الموظف الفردية 2%">
        </div>
        <div class="col-md-2 form-group">
          <label>
            الكود
            <small class="text-muted">(يولَّد تلقائياً إن تُرك فارغاً)</small>
          </label>
          <div class="input-group">
            <input type="text" name="code" class="form-control" id="ruleCode"
              value="{{ old('code') }}" placeholder="مثال: COM-SALES-01">
            <div class="input-group-append">
              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="autoCode()" title="توليد تلقائي">
                <i class="fas fa-magic"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-3 form-group">
          <label>الفرع (اختياري)</label>
          <select name="branch_id" class="form-control">
            <option value="">كل الفروع</option>
            @foreach($branches as $br)
              <option value="{{ $br->id }}" {{ old('branch_id')==$br->id?'selected':'' }}>
                {{ $br->branch_name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>الوصف</label>
        <input type="text" name="description" class="form-control"
          value="{{ old('description') }}" placeholder="شرح مختصر للقاعدة...">
      </div>

      <hr>
      {{-- أساس الاحتساب --}}
      <h6 class="font-weight-bold mb-3 text-primary">① أساس الاحتساب — ما الذي تُحتسب عليه العمولة؟</h6>
      <div class="row mb-3">
        @foreach([
          ['individual_sales','👤','مبيعات الموظف الفردية','العمولة بناءً على مبيعات الموظف نفسه'],
          ['branch_sales','🏢','إجمالي مبيعات الفرع','العمولة على إجمالي مبيعات الفرع'],
          ['area_sales','🗺','مبيعات المنطقة','إجمالي مبيعات كل فروع المنطقة'],
          ['company_sales','🏭','مبيعات الشركة كاملة','إجمالي مبيعات الشركة'],
          ['fixed','💰','مبلغ ثابت','عمولة ثابتة بغض النظر عن المبيعات'],
          ['kpi_based','📊','مرتبط بـ KPI','عمولة مرتبطة بتحقيق مؤشر أداء'],
        ] as [$val,$icon,$name,$desc])
        <div class="col-md-2 mb-2">
          <div class="option-card {{ old('basis','individual_sales')==$val?'selected':'' }}"
               onclick="selectBasis('{{ $val }}', this)">
            <div class="icon">{{ $icon }}</div>
            <div class="label">{{ $name }}</div>
            <small class="text-muted" style="font-size:.72em">{{ $desc }}</small>
          </div>
        </div>
        @endforeach
      </div>
      <input type="hidden" name="basis" id="basisInput" value="{{ old('basis','individual_sales') }}">

      <hr>
      {{-- المستفيد: المستوى الوظيفي --}}
      <h6 class="font-weight-bold mb-3 text-primary">② من يستلم هذه العمولة؟ (المستوى الوظيفي)</h6>
      @if(($orgLevels ?? collect())->isEmpty())
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle ml-1"></i>
          لم يتم إنشاء هيكل وظيفي بعد.
          <a href="{{ route('org_levels.create') }}" class="btn btn-sm btn-warning mr-2">إنشاء الهيكل الوظيفي</a>
          — بدونه ستُطبَّق العمولة على جميع الموظفين الذين لديهم مبيعات.
        </div>
      @else
        <div class="alert alert-info py-2 mb-3" style="font-size:.88em">
          <i class="fas fa-info-circle ml-1"></i>
          اختر المستوى الوظيفي الذي تستحق هذه العمولة. مثلاً: "عمولة البائع" تُحدد لمستوى <strong>بائع/مندوب</strong>،
          و"عمولة المدير" تُحدد لمستوى <strong>مدير مبيعات</strong>.
          اتركه فارغاً لتطبيق العمولة على جميع المستويات.
        </div>
        <div class="row mb-3">
          <div class="col-md-6 form-group">
            <label>المستوى الوظيفي المستحق للعمولة</label>
            <select name="org_level_id" class="form-control">
              <option value="">— جميع المستويات (بدون تحديد) —</option>
              @foreach($orgLevels as $lv)
                <option value="{{ $lv->id }}" {{ old('org_level_id') == $lv->id ? 'selected' : '' }}>
                  {{ str_repeat('— ', $lv->level_order - 1) }}{{ $lv->name }}
                  @if($lv->receives_seller_commission) ✓ عمولة بائع @endif
                  @if($lv->receives_manager_commission) ✓ عمولة مدير @endif
                </option>
              @endforeach
            </select>
            <small class="text-muted">يحدد من يستحق هذه القاعدة من الموظفين حسب مستواهم الوظيفي</small>
          </div>
          <div class="col-md-6">
            <div class="card border-light bg-light p-2 mt-3" style="font-size:.85em">
              <strong>المستويات وخصائصها:</strong>
              <ul class="mb-0 mt-1 pr-3">
                @foreach($orgLevels as $lv)
                <li>
                  <strong>{{ $lv->name }}</strong>
                  @if($lv->receives_seller_commission)
                    <span class="badge bg-success text-white ml-1">عمولة بائع</span>
                  @endif
                  @if($lv->receives_manager_commission)
                    <span class="badge bg-primary text-white ml-1">عمولة مدير</span>
                  @endif
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      @endif
      {{-- حفظ recipient_type للتوافق --}}
      <input type="hidden" name="recipient_type" value="employee">

      <hr>
      {{-- طريقة الحساب --}}
      <h6 class="font-weight-bold mb-3 text-primary">③ طريقة حساب قيمة العمولة</h6>
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="option-card {{ old('calc_type','percentage')=='percentage'?'selected':'' }}"
               onclick="selectCalcType('percentage', this)">
            <div class="icon">%</div>
            <div class="label">نسبة مئوية ثابتة</div>
            <small class="text-muted">مثال: 2% من المبيعات</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="option-card {{ old('calc_type')=='fixed_amount'?'selected':'' }}"
               onclick="selectCalcType('fixed_amount', this)">
            <div class="icon">💵</div>
            <div class="label">مبلغ ثابت</div>
            <small class="text-muted">مثال: 500 ج.م شهرياً</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="option-card {{ old('calc_type')=='tiered'?'selected':'' }}"
               onclick="selectCalcType('tiered', this)">
            <div class="icon">📶</div>
            <div class="label">متدرج (شرائح)</div>
            <small class="text-muted">نسبة مختلفة لكل شريحة مبيعات</small>
          </div>
        </div>
      </div>
      <input type="hidden" name="calc_type" id="calcTypeInput" value="{{ old('calc_type','percentage') }}">

      {{-- حقل النسبة --}}
      <div id="pctField" class="row {{ old('calc_type','percentage')!='percentage'?'d-none':'' }}">
        <div class="col-md-4 form-group">
          <label>النسبة المئوية <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="percentage" class="form-control" step="0.001" min="0" max="100"
              value="{{ old('percentage', 0) }}" placeholder="مثال: 2.5">
            <div class="input-group-append"><span class="input-group-text">%</span></div>
          </div>
          <small class="text-muted">تُضرب في قيمة المبيعات الأساسية المحددة أعلاه</small>
        </div>
      </div>

      {{-- حقل المبلغ الثابت --}}
      <div id="fixedField" class="row {{ old('calc_type')!='fixed_amount'?'d-none':'' }}">
        <div class="col-md-4 form-group">
          <label>المبلغ الثابت <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="number" name="fixed_amount" class="form-control" step="0.01" min="0"
              value="{{ old('fixed_amount', 0) }}" placeholder="مثال: 500">
            <div class="input-group-append"><span class="input-group-text">ج.م</span></div>
          </div>
        </div>
      </div>

      {{-- جدول الشرائح --}}
      <div id="tieredField" class="{{ old('calc_type')!='tiered'?'d-none':'' }}">
        <label class="font-weight-bold">شرائح العمولة</label>
        <div id="tiersContainer">
          @if(old('tier_from'))
            @foreach(old('tier_from') as $i => $from)
            <div class="tier-row d-flex align-items-center gap-2">
              <span class="text-muted ml-1" style="min-width:30px">{{ $i+1 }}</span>
              <div class="input-group mr-1" style="max-width:160px">
                <div class="input-group-prepend"><span class="input-group-text">من</span></div>
                <input type="number" name="tier_from[]" class="form-control" value="{{ $from }}" min="0">
              </div>
              <div class="input-group mr-1" style="max-width:160px">
                <div class="input-group-prepend"><span class="input-group-text">إلى</span></div>
                <input type="number" name="tier_to[]" class="form-control" value="{{ old('tier_to')[$i] }}" placeholder="∞">
              </div>
              <div class="input-group mr-1" style="max-width:140px">
                <input type="number" name="tier_pct[]" class="form-control" value="{{ old('tier_pct')[$i] }}" step="0.001" placeholder="النسبة">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
              <button type="button" class="btn btn-sm btn-danger" onclick="removeTier(this)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
            @endforeach
          @else
            <div class="tier-row d-flex align-items-center">
              <span class="text-muted ml-2" style="min-width:30px">1</span>
              <div class="input-group ml-1" style="max-width:160px">
                <div class="input-group-prepend"><span class="input-group-text">من</span></div>
                <input type="number" name="tier_from[]" class="form-control" value="0" min="0">
              </div>
              <div class="input-group ml-1" style="max-width:160px">
                <div class="input-group-prepend"><span class="input-group-text">إلى</span></div>
                <input type="number" name="tier_to[]" class="form-control" placeholder="∞">
              </div>
              <div class="input-group ml-1" style="max-width:140px">
                <input type="number" name="tier_pct[]" class="form-control" step="0.001" placeholder="النسبة">
                <div class="input-group-append"><span class="input-group-text">%</span></div>
              </div>
              <button type="button" class="btn btn-sm btn-danger mr-1" onclick="removeTier(this)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="addTier()">
          <i class="fas fa-plus ml-1"></i>إضافة شريحة
        </button>
        <div class="alert alert-info mt-2 py-2" style="font-size:.85em">
          <i class="fas fa-info-circle ml-1"></i>
          مثال: من 0 إلى 10000 بنسبة 1% | من 10001 فأكثر بنسبة 2%
        </div>
      </div>

    </div>{{-- end card-body --}}

    <div class="card-footer">
      <button type="submit" class="btn btn-success">
        <i class="fas fa-save ml-1"></i>حفظ القاعدة
      </button>
      <a href="{{ route('commissions_v2.rules') }}" class="btn btn-secondary mr-2">رجوع</a>
    </div>
  </form>
</div>
</div>
@endsection

@section('script')
<script>
// ── توليد كود تلقائي ──
function autoCode() {
  const name = document.getElementById('ruleName').value.trim();
  if (!name) { alert('أدخل اسم القاعدة أولاً'); return; }
  const slug = name.replace(/[؀-ۿ]/g, c => {
    const m = {'ب':'B','ت':'T','س':'S','م':'M','ع':'A','ل':'L','ن':'N','ر':'R','ف':'F','ك':'K','د':'D','و':'W','ح':'H','ق':'Q','ي':'Y','ج':'J','خ':'KH','غ':'GH','ش':'SH','ص':'S','ض':'D','ط':'T','ز':'Z','ه':'H'};
    return m[c] || '';
  }).replace(/[^A-Za-z0-9]/g, '').toUpperCase().slice(0, 8);
  const ts = Date.now().toString().slice(-4);
  document.getElementById('ruleCode').value = 'COM-' + (slug || 'RULE') + '-' + ts;
}

// ── اختيار الأساس ──
function selectBasis(val, el) {
  document.querySelectorAll('[onclick^="selectBasis"]').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('basisInput').value = val;
}

// ── اختيار المستفيد ──
function selectRecipient(val, el) {
  document.querySelectorAll('[onclick^="selectRecipient"]').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  el.querySelector('input[type=radio]').checked = true;
}

// ── اختيار طريقة الحساب ──
function selectCalcType(val, el) {
  document.querySelectorAll('[onclick^="selectCalcType"]').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('calcTypeInput').value = val;

  document.getElementById('pctField').classList.toggle('d-none',    val !== 'percentage');
  document.getElementById('fixedField').classList.toggle('d-none',  val !== 'fixed_amount');
  document.getElementById('tieredField').classList.toggle('d-none', val !== 'tiered');
}

// ── الشرائح ──
let tierCount = {{ old('tier_from') ? count(old('tier_from')) : 1 }};

function addTier() {
  tierCount++;
  const container = document.getElementById('tiersContainer');
  const div = document.createElement('div');
  div.className = 'tier-row d-flex align-items-center';
  div.innerHTML = `
    <span class="text-muted ml-2" style="min-width:30px">${tierCount}</span>
    <div class="input-group ml-1" style="max-width:160px">
      <div class="input-group-prepend"><span class="input-group-text">من</span></div>
      <input type="number" name="tier_from[]" class="form-control" min="0">
    </div>
    <div class="input-group ml-1" style="max-width:160px">
      <div class="input-group-prepend"><span class="input-group-text">إلى</span></div>
      <input type="number" name="tier_to[]" class="form-control" placeholder="∞">
    </div>
    <div class="input-group ml-1" style="max-width:140px">
      <input type="number" name="tier_pct[]" class="form-control" step="0.001" placeholder="النسبة">
      <div class="input-group-append"><span class="input-group-text">%</span></div>
    </div>
    <button type="button" class="btn btn-sm btn-danger mr-1" onclick="removeTier(this)">
      <i class="fas fa-trash"></i>
    </button>`;
  container.appendChild(div);
}

function removeTier(btn) {
  const rows = document.querySelectorAll('.tier-row');
  if (rows.length <= 1) { alert('يجب أن تكون هناك شريحة واحدة على الأقل'); return; }
  btn.closest('.tier-row').remove();
}
</script>
@endsection
