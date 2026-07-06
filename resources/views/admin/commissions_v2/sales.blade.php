{{-- FILE: resources/views/admin/commissions_v2/sales.blade.php --}}
@extends('admin.layouts.admin')
@section('title') إدخال مبيعات الشهر @endsection
@section('start') الرواتب والمؤثرات @endsection
@section('home') <a href="{{ route('commissions_v2.rules') }}">قواعد العمولات</a> @endsection
@section('startpage') إدخال المبيعات @endsection

@section('css')
<style>
.branch-badge { font-size:.78em; padding:3px 8px; border-radius:10px; }
.days-badge   { background:#e3f2fd; color:#1565c0; font-size:.8em; padding:2px 7px; border-radius:8px; }
.emp-total    { font-size:.82em; color:#6c757d; }
.summary-card { border-radius:8px; padding:10px 14px; text-align:center; }
</style>
@endsection

@section('content')
<div class="col-12">

  {{-- ── شريط الفلتر ── --}}
  <div class="card card-outline card-success mb-3">
    <div class="card-body py-2">
      <div class="d-flex align-items-center flex-wrap" style="gap:10px">
        <form method="GET" class="form-inline">
          <label class="ml-2 font-weight-bold text-nowrap">الشهر:</label>
          <select name="month" class="form-control form-control-sm ml-2">
            @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $i => $m)
              <option value="{{ $i+1 }}" {{ $month == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
            @endforeach
          </select>
          <input type="number" name="year" class="form-control form-control-sm mr-1 ml-2"
            style="width:80px" value="{{ $year }}">
          <button type="submit" class="btn btn-sm btn-primary">
            <i class="fas fa-search"></i>
          </button>
        </form>

        <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#addSaleModal">
          <i class="fas fa-plus ml-1"></i>إضافة سجل مبيعات
        </button>

        <a href="{{ route('commissions_v2.calculate', ['month'=>$month,'year'=>$year]) }}"
           class="btn btn-primary btn-sm">
          <i class="fas fa-calculator ml-1"></i>احتساب العمولات
        </a>
        <a href="{{ route('branch_commissions.calculate', ['month'=>$month,'year'=>$year]) }}"
           class="btn btn-outline-primary btn-sm">
          <i class="fas fa-store ml-1"></i>عمولات الفروع
        </a>
      </div>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  @php
    $monthNames = ['','يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
    $byBranch   = $records->groupBy('branch_id');
  @endphp

  {{-- ── ملخص سريع للفروع ── --}}
  @if($records->isNotEmpty())
  <div class="row mb-3">
    @foreach($byBranch as $branchId => $branchRecords)
    <div class="col-md-2 col-sm-4 mb-2">
      <div class="summary-card bg-light border">
        <div style="font-size:.82em; font-weight:600; color:#333">
          {{ $branchRecords->first()->branch->branch_name ?? 'غير محدد' }}
        </div>
        <div style="font-size:1.1em; font-weight:700; color:#28a745">
          {{ number_format($branchRecords->sum('sales_amount'), 0) }}
        </div>
        <div style="font-size:.75em; color:#6c757d">
          {{ $branchRecords->count() }} سجل | {{ $branchRecords->groupBy('employee_id')->count() }} موظف
        </div>
      </div>
    </div>
    @endforeach
    <div class="col-md-2 col-sm-4 mb-2">
      <div class="summary-card bg-success text-white">
        <div style="font-size:.82em; font-weight:600">الإجمالي الكلي</div>
        <div style="font-size:1.1em; font-weight:700">
          {{ number_format($records->sum('sales_amount'), 0) }}
        </div>
        <div style="font-size:.75em; opacity:.85">{{ $records->count() }} سجل</div>
      </div>
    </div>
  </div>
  @endif

  {{-- ── جدول السجلات ── --}}
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-table ml-2 text-success"></i>
        سجلات مبيعات {{ $monthNames[$month] }} {{ $year }}
      </h3>
      <div class="card-tools">
        <input type="text" id="tableSearch" class="form-control form-control-sm"
          placeholder="🔍 بحث..." style="width:180px" oninput="filterTable(this.value)">
      </div>
    </div>

    @if($records->isEmpty())
    <div class="card-body text-center py-5">
      <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
      <h5 class="text-muted">لا توجد سجلات مبيعات لهذا الشهر</h5>
      <button type="button" class="btn btn-success mt-2" data-toggle="modal" data-target="#addSaleModal">
        <i class="fas fa-plus ml-1"></i>إضافة أول سجل
      </button>
    </div>
    @else
    <div class="card-body p-0">
    <div class="table-responsive">
    <table class="table table-bordered table-hover mb-0" id="salesTable">
      <thead class="thead-dark">
        <tr>
          <th style="width:35px">#</th>
          <th>الموظف</th>
          <th>الفرع</th>
          <th class="text-center">الأيام</th>
          <th class="text-center">قيمة المبيعات</th>
          <th>ملاحظات</th>
          <th class="text-center">تاريخ الإدخال</th>
          <th class="text-center" style="width:90px">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        @foreach($records as $rec)
        <tr data-search="{{ strtolower($rec->employee->employee_name_A ?? '') }} {{ strtolower($rec->branch->branch_name ?? '') }}">
          <td class="text-center text-muted">{{ $loop->iteration }}</td>
          <td>
            <strong>{{ $rec->employee->employee_name_A ?? '—' }}</strong>
            <br><small class="text-muted">{{ $rec->employee->employee_id ?? '' }}</small>
          </td>
          <td>
            <span class="badge badge-secondary branch-badge">
              {{ $rec->branch->branch_name ?? '—' }}
            </span>
          </td>
          <td class="text-center">
            @if($rec->from_day || $rec->to_day)
              <span class="days-badge">
                {{ $rec->from_day ?? 1 }} — {{ $rec->to_day ?? $daysInMonth }}
                <small>({{ ($rec->to_day ?? $daysInMonth) - ($rec->from_day ?? 1) + 1 }} يوم)</small>
              </span>
            @else
              <span class="text-muted" style="font-size:.82em">الشهر كامل</span>
            @endif
          </td>
          <td class="text-center">
            <strong class="text-success" style="font-size:1.05em">
              {{ number_format($rec->sales_amount, 2) }}
            </strong>
            <small class="text-muted">ج.م</small>
          </td>
          <td>
            <small class="text-muted">{{ $rec->notes ?? '—' }}</small>
          </td>
          <td class="text-center">
            <small class="text-muted">{{ $rec->updated_at?->format('d/m H:i') }}</small>
          </td>
          <td class="text-center">
            <button type="button" class="btn btn-xs btn-warning"
              onclick="openEditModal({{ $rec->id }}, {{ $rec->employee_id }}, {{ $rec->branch_id ?? 'null' }}, '{{ $rec->sales_amount }}', '{{ $rec->from_day ?? '' }}', '{{ $rec->to_day ?? '' }}', '{{ addslashes($rec->notes ?? '') }}')"
              title="تعديل">
              <i class="fas fa-edit"></i>
            </button>
            <a href="{{ route('commissions_v2.delete_sale', $rec->id) }}"
               class="btn btn-xs btn-danger mr-1"
               onclick="return confirm('حذف هذا السجل؟')"
               title="حذف">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="table-success font-weight-bold">
          <td colspan="4" class="text-left">الإجمالي — {{ $records->count() }} سجل</td>
          <td class="text-center">{{ number_format($records->sum('sales_amount'), 2) }} ج.م</td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
    </div>
    </div>

    {{-- إجمالي لكل موظف --}}
    @php
      $byEmployee = $records->groupBy('employee_id');
      $multiEntry = $byEmployee->filter(fn($r) => $r->count() > 1);
    @endphp
    @if($multiEntry->isNotEmpty())
    <div class="card-footer py-2">
      <small class="text-muted font-weight-bold">موظفون بأكثر من سجل:</small>
      @foreach($multiEntry as $empId => $empRecs)
        <span class="badge badge-info mr-1">
          {{ $empRecs->first()->employee->employee_name_A ?? '—' }}:
          {{ number_format($empRecs->sum('sales_amount'), 0) }} ج.م
          ({{ $empRecs->count() }} سجل)
        </span>
      @endforeach
    </div>
    @endif
    @endif
  </div>

</div>

{{-- ════════════════════════════════════════
     Modal: إضافة سجل مبيعات
════════════════════════════════════════ --}}
<div class="modal fade" id="addSaleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('commissions_v2.store_sale') }}" method="POST">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year"  value="{{ $year }}">

        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="fas fa-plus-circle ml-2"></i>إضافة سجل مبيعات جديد
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            {{-- الموظف --}}
            <div class="col-md-6 form-group">
              <label>الموظف <span class="text-danger">*</span></label>
              <select name="employee_id" class="form-control" required>
                <option value="">— اختر الموظف —</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->id }}">
                    {{ $emp->employee_name_A }} ({{ $emp->employee_id }})
                  </option>
                @endforeach
              </select>
            </div>
            {{-- الفرع --}}
            <div class="col-md-6 form-group">
              <label>الفرع <span class="text-danger">*</span></label>
              <select name="branch_id" class="form-control" required>
                <option value="">— اختر الفرع —</option>
                @foreach($branches as $br)
                  <option value="{{ $br->id }}">{{ $br->branch_name }}</option>
                @endforeach
              </select>
            </div>
            {{-- من يوم --}}
            <div class="col-md-3 form-group">
              <label>من يوم</label>
              <input type="number" name="from_day" class="form-control"
                min="1" max="{{ $daysInMonth }}" placeholder="مثال: 1"
                oninput="calcDays('add')">
              <small class="text-muted">أول يوم في الفترة</small>
            </div>
            {{-- إلى يوم --}}
            <div class="col-md-3 form-group">
              <label>إلى يوم</label>
              <input type="number" name="to_day" class="form-control"
                min="1" max="{{ $daysInMonth }}" placeholder="مثال: {{ $daysInMonth }}"
                oninput="calcDays('add')">
              <small class="text-muted">آخر يوم في الفترة</small>
            </div>
            {{-- عدد الأيام --}}
            <div class="col-md-2 form-group">
              <label>عدد الأيام</label>
              <input type="text" class="form-control bg-light" id="addDaysCount"
                readonly placeholder="—">
            </div>
            {{-- قيمة المبيعات --}}
            <div class="col-md-4 form-group">
              <label>قيمة المبيعات <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" name="sales_amount" class="form-control"
                  step="0.01" min="0" required placeholder="0.00">
                <div class="input-group-append">
                  <span class="input-group-text">ج.م</span>
                </div>
              </div>
            </div>
            {{-- ملاحظات --}}
            <div class="col-md-12 form-group">
              <label>ملاحظات</label>
              <input type="text" name="notes" class="form-control"
                placeholder="مثال: مبيعات أحمد رجب خلال فترة عمله ببدلة دمياط...">
            </div>
          </div>

          {{-- تلميح --}}
          <div class="alert alert-info py-2 mb-0" style="font-size:.85em">
            <i class="fas fa-lightbulb ml-1"></i>
            <strong>تلميح:</strong> إذا عمل الموظف في فرعين خلال الشهر، أضف سجلَين منفصلَين —
            كل سجل بفرعه وأيامه المحددة.
            <br>
            مثال: أحمد رجب → سجل 1: المنصورة بدلة (1-19) + سجل 2: دمياط بدلة (20-{{ $daysInMonth }})
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save ml-1"></i>حفظ السجل
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════
     Modal: تعديل سجل مبيعات
════════════════════════════════════════ --}}
<div class="modal fade" id="editSaleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editSaleForm" method="POST">
        @csrf
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">
            <i class="fas fa-edit ml-2"></i>تعديل سجل مبيعات
          </h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group">
              <label>الموظف <span class="text-danger">*</span></label>
              <select name="employee_id" id="editEmployee" class="form-control" required>
                <option value="">— اختر الموظف —</option>
                @foreach($employees as $emp)
                  <option value="{{ $emp->id }}">
                    {{ $emp->employee_name_A }} ({{ $emp->employee_id }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6 form-group">
              <label>الفرع <span class="text-danger">*</span></label>
              <select name="branch_id" id="editBranch" class="form-control" required>
                <option value="">— اختر الفرع —</option>
                @foreach($branches as $br)
                  <option value="{{ $br->id }}">{{ $br->branch_name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-3 form-group">
              <label>من يوم</label>
              <input type="number" name="from_day" id="editFromDay" class="form-control"
                min="1" max="{{ $daysInMonth }}" placeholder="1"
                oninput="calcDays('edit')">
            </div>
            <div class="col-md-3 form-group">
              <label>إلى يوم</label>
              <input type="number" name="to_day" id="editToDay" class="form-control"
                min="1" max="{{ $daysInMonth }}" placeholder="{{ $daysInMonth }}"
                oninput="calcDays('edit')">
            </div>
            <div class="col-md-2 form-group">
              <label>عدد الأيام</label>
              <input type="text" class="form-control bg-light" id="editDaysCount" readonly placeholder="—">
            </div>
            <div class="col-md-4 form-group">
              <label>قيمة المبيعات <span class="text-danger">*</span></label>
              <div class="input-group">
                <input type="number" name="sales_amount" id="editAmount" class="form-control"
                  step="0.01" min="0" required>
                <div class="input-group-append">
                  <span class="input-group-text">ج.م</span>
                </div>
              </div>
            </div>
            <div class="col-md-12 form-group">
              <label>ملاحظات</label>
              <input type="text" name="notes" id="editNotes" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save ml-1"></i>حفظ التعديل
          </button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('script')
<script>
// ── حساب عدد الأيام ──
function calcDays(prefix) {
  const from  = parseInt(document.getElementById(prefix === 'add' ? 'addSaleModal' : 'editSaleModal').querySelector('[name="from_day"]').value) || 0;
  const to    = parseInt(document.getElementById(prefix === 'add' ? 'addSaleModal' : 'editSaleModal').querySelector('[name="to_day"]').value) || 0;
  const countEl = document.getElementById(prefix + 'DaysCount');
  if (from && to && to >= from) {
    countEl.value = (to - from + 1) + ' يوم';
    countEl.classList.remove('text-danger');
  } else if (from && to && to < from) {
    countEl.value = '! خطأ';
    countEl.classList.add('text-danger');
  } else {
    countEl.value = '';
  }
}

// ── فتح modal التعديل ──
function openEditModal(id, empId, branchId, amount, fromDay, toDay, notes) {
  const form = document.getElementById('editSaleForm');
  form.action = '{{ url('admin/dashboard/commissions_v2/sales/update') }}/' + id;

  document.getElementById('editEmployee').value = empId || '';
  document.getElementById('editBranch').value   = branchId || '';
  document.getElementById('editAmount').value   = amount  || '';
  document.getElementById('editFromDay').value  = fromDay || '';
  document.getElementById('editToDay').value    = toDay   || '';
  document.getElementById('editNotes').value    = notes   || '';

  calcDays('edit');
  $('#editSaleModal').modal('show');
}

// ── البحث في الجدول ──
function filterTable(query) {
  const q = query.trim().toLowerCase();
  document.querySelectorAll('#salesTable tbody tr').forEach(tr => {
    const text = (tr.dataset.search || '');
    tr.style.display = (!q || text.includes(q)) ? '' : 'none';
  });
}
</script>
@endsection
