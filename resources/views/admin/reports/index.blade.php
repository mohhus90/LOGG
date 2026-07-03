@extends('admin.layouts.admin')
@section('title') التقارير @endsection
@section('start') التقارير @endsection
@section('home') <a href="{{ route('reports.index') }}">التقارير</a> @endsection
@section('startpage') عرض @endsection

@section('css')
<style>
.report-card { transition:.2s; cursor:pointer; border:2px solid transparent; }
.report-card:hover { border-color:var(--primary,#3490dc); transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.12); }
.report-card .card-body { padding:30px 20px; text-align:center; }
.report-card i { font-size:2.5rem; margin-bottom:10px; }
.export-form select, .export-form input { margin-bottom:8px; }
</style>
@endsection

@section('content')
<div class="col-12">
  <h4 class="mb-4"><i class="fas fa-chart-bar ml-2 text-primary"></i>التقارير والتصدير</h4>

  <div class="row">

    {{-- ══ الحضور ══ --}}
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#attendanceForm">
        <div class="card-body">
          <i class="fas fa-fingerprint text-primary"></i>
          <h5 class="mt-2 mb-0">الحضور والانصراف</h5>
          <small class="text-muted">Excel / طباعة PDF — مع كامل التفاصيل</small>
        </div>
      </div>
      <div class="collapse show mt-2" id="attendanceForm">
        <div class="card export-form border-primary">
          <div class="card-header bg-primary text-white py-1 px-2" style="font-size:.82rem">
            <i class="fas fa-filter ml-1"></i> فلاتر التقرير
          </div>
          <div class="card-body py-2">
            <form action="{{ route('reports.attendance') }}" method="GET" target="_blank">

              <label class="mb-0" style="font-size:.78rem;color:#555">الموظف</label>
              <select name="employee_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>

              <label class="mb-0" style="font-size:.78rem;color:#555">الإدارة</label>
              <select name="department_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الإدارات --</option>
                @foreach($departments as $d)
                  <option value="{{ $d->id }}">{{ $d->dep_name }}</option>
                @endforeach
              </select>

              <div class="row mx-0" style="gap:0">
                <div class="col-6 pl-1 pr-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">من تاريخ</label>
                  <input type="date" name="from_date" class="form-control form-control-sm mb-2">
                </div>
                <div class="col-6 pr-1 pl-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">إلى تاريخ</label>
                  <input type="date" name="to_date" class="form-control form-control-sm mb-2">
                </div>
              </div>

              <label class="mb-0" style="font-size:.78rem;color:#555">الحالة</label>
              <select name="status" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الحالات --</option>
                <option value="1">حضر</option>
                <option value="2">غياب</option>
                <option value="3">إجازة</option>
                <option value="4">إجازة رسمية</option>
                <option value="5">مأمورية</option>
                <option value="6">إجازة أسبوعية</option>
              </select>

              <label class="mb-0" style="font-size:.78rem;color:#555">
                <i class="fas fa-sort ml-1"></i>ترتيب النتائج
              </label>
              <select name="sort_by" class="form-control form-control-sm mb-3">
                <option value="date_desc">التاريخ — الأحدث أولاً</option>
                <option value="date_asc">التاريخ — الأقدم أولاً</option>
                <option value="name_asc">الاسم — أ إلى ي</option>
                <option value="name_desc">الاسم — ي إلى أ</option>
              </select>

              <div class="btn-group w-100">
                <button class="btn btn-sm btn-success" name="format" value="excel">
                  <i class="fas fa-file-excel ml-1"></i> Excel
                </button>
                <button class="btn btn-sm btn-danger" name="format" value="pdf">
                  <i class="fas fa-print ml-1"></i> طباعة PDF
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ الموظفون ══ --}}
    <div class="col-md-6 col-lg-3 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#employeesForm">
        <div class="card-body">
          <i class="fas fa-users text-success"></i>
          <h5 class="mt-2 mb-0">الموظفون</h5>
          <small class="text-muted">Excel / طباعة PDF</small>
        </div>
      </div>
      <div class="collapse mt-2" id="employeesForm">
        <div class="card export-form">
          <div class="card-body">
            <form action="{{ route('reports.employees') }}" method="GET" target="_blank">
              <select name="department_id" class="form-control form-control-sm">
                <option value="">-- جميع الإدارات --</option>
                @foreach($departments as $d)
                  <option value="{{ $d->id }}">{{ $d->dep_name }}</option>
                @endforeach
              </select>
              <div class="btn-group w-100 mt-2">
                <button class="btn btn-sm btn-success" name="format" value="excel"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-sm btn-danger"  name="format" value="pdf"><i class="fas fa-print"></i> PDF</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ السلف ══ --}}
    <div class="col-md-6 col-lg-4 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#advancesForm">
        <div class="card-body">
          <i class="fas fa-hand-holding-usd text-warning"></i>
          <h5 class="mt-2 mb-0">السلف</h5>
          <small class="text-muted">Excel / طباعة PDF — مع فلترة وترتيب</small>
        </div>
      </div>
      <div class="collapse mt-2" id="advancesForm">
        <div class="card export-form border-warning">
          <div class="card-header bg-warning text-dark py-1 px-2" style="font-size:.82rem">
            <i class="fas fa-filter ml-1"></i> فلاتر التقرير
          </div>
          <div class="card-body py-2">
            <form action="{{ route('reports.advances') }}" method="GET" target="_blank">

              <label class="mb-0" style="font-size:.78rem;color:#555">الموظف</label>
              <select name="employee_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>

              @if(isset($branches) && $branches->count())
              <label class="mb-0" style="font-size:.78rem;color:#555">الفرع</label>
              <select name="branch_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الفروع --</option>
                @foreach($branches as $br)
                  <option value="{{ $br->id }}">{{ $br->branch_name }}</option>
                @endforeach
              </select>
              @endif

              <label class="mb-0" style="font-size:.78rem;color:#555">الحالة</label>
              <select name="status" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الحالات --</option>
                <option value="1">جارية</option>
                <option value="2">مسددة</option>
                <option value="3">ملغاة</option>
              </select>

              <div class="row mx-0" style="gap:0">
                <div class="col-6 pl-1 pr-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">من تاريخ</label>
                  <input type="date" name="from_date" class="form-control form-control-sm mb-2">
                </div>
                <div class="col-6 pr-1 pl-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">إلى تاريخ</label>
                  <input type="date" name="to_date" class="form-control form-control-sm mb-2">
                </div>
              </div>

              <label class="mb-0" style="font-size:.78rem;color:#555">
                <i class="fas fa-sort ml-1"></i>ترتيب النتائج
              </label>
              <select name="sort_by" class="form-control form-control-sm mb-3">
                <option value="date_desc">التاريخ — الأحدث أولاً</option>
                <option value="date_asc">التاريخ — الأقدم أولاً</option>
                <option value="name_asc">الاسم — أ إلى ي</option>
                <option value="name_desc">الاسم — ي إلى أ</option>
              </select>

              <div class="btn-group w-100">
                <button class="btn btn-sm btn-success" name="format" value="excel">
                  <i class="fas fa-file-excel ml-1"></i> Excel
                </button>
                <button class="btn btn-sm btn-danger" name="format" value="pdf">
                  <i class="fas fa-print ml-1"></i> طباعة PDF
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ الإجازات ══ --}}
    <div class="col-md-6 col-lg-3 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#vacationsForm">
        <div class="card-body">
          <i class="fas fa-umbrella-beach text-info"></i>
          <h5 class="mt-2 mb-0">أرصدة الإجازات</h5>
          <small class="text-muted">Excel / طباعة PDF</small>
        </div>
      </div>
      <div class="collapse mt-2" id="vacationsForm">
        <div class="card export-form">
          <div class="card-body">
            <form action="{{ route('reports.vacations') }}" method="GET" target="_blank">
              <select name="employee_id" class="form-control form-control-sm">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>
              <div class="btn-group w-100 mt-2">
                <button class="btn btn-sm btn-success" name="format" value="excel"><i class="fas fa-file-excel"></i> Excel</button>
                <button class="btn btn-sm btn-danger"  name="format" value="pdf"><i class="fas fa-print"></i> PDF</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ العمولات ══ --}}
    <div class="col-md-6 col-lg-5 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#commissionsForm" style="border-color:#1a6f3c">
        <div class="card-body">
          <i class="fas fa-percentage text-success"></i>
          <h5 class="mt-2 mb-0">العمولات</h5>
          <small class="text-muted">Excel / طباعة PDF — مع فلترة وترتيب</small>
        </div>
      </div>
      <div class="collapse mt-2" id="commissionsForm">
        <div class="card export-form border-success">
          <div class="card-header bg-success text-white py-1 px-2" style="font-size:.82rem">
            <i class="fas fa-filter ml-1"></i> فلاتر تقرير العمولات
          </div>
          <div class="card-body py-2">
            <form action="{{ route('reports.commissions') }}" method="GET" target="_blank">

              <label class="mb-0" style="font-size:.78rem;color:#555">الموظف</label>
              <select name="employee_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>

              <div class="row mx-0">
                <div class="col-6 pl-1 pr-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">الشهر</label>
                  <select name="month" class="form-control form-control-sm mb-2">
                    <option value="">-- الكل --</option>
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $mi => $mn)
                      <option value="{{ $mi+1 }}">{{ $mn }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-6 pr-1 pl-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">السنة</label>
                  <input type="number" name="year" class="form-control form-control-sm mb-2"
                    placeholder="السنة" value="{{ now()->year }}">
                </div>
              </div>

              <label class="mb-0" style="font-size:.78rem;color:#555">الحالة</label>
              <select name="status" class="form-control form-control-sm mb-2">
                <option value="">-- الكل --</option>
                <option value="1">معتمدة</option>
                <option value="2">معلقة</option>
                <option value="3">ملغاة</option>
              </select>

              @if(isset($commissionTypes) && $commissionTypes->count())
              <label class="mb-0" style="font-size:.78rem;color:#555">نوع العمولة</label>
              <select name="commission_type" class="form-control form-control-sm mb-2">
                <option value="">-- الكل --</option>
                @foreach($commissionTypes as $ct)
                  <option value="{{ $ct }}">{{ $ct }}</option>
                @endforeach
              </select>
              @endif

              <label class="mb-0" style="font-size:.78rem;color:#555">
                <i class="fas fa-sort ml-1"></i>ترتيب النتائج
              </label>
              <select name="sort_by" class="form-control form-control-sm mb-3">
                <option value="date_desc">التاريخ — الأحدث أولاً</option>
                <option value="date_asc">التاريخ — الأقدم أولاً</option>
                <option value="amount_desc">المبلغ — الأعلى أولاً</option>
                <option value="amount_asc">المبلغ — الأقل أولاً</option>
                <option value="month_desc">الشهر — الأحدث أولاً</option>
                <option value="month_asc">الشهر — الأقدم أولاً</option>
              </select>

              <div class="btn-group w-100">
                <button class="btn btn-sm btn-success" name="format" value="excel">
                  <i class="fas fa-file-excel ml-1"></i> Excel
                </button>
                <button class="btn btn-sm btn-danger" name="format" value="pdf">
                  <i class="fas fa-print ml-1"></i> طباعة PDF
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ مؤشرات الأداء KPI ══ --}}
    <div class="col-md-6 col-lg-5 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#kpiForm" style="border-color:#4e1f88">
        <div class="card-body">
          <i class="fas fa-chart-line" style="color:#4e1f88"></i>
          <h5 class="mt-2 mb-0">مؤشرات الأداء KPI</h5>
          <small class="text-muted">Excel / طباعة PDF — تفصيلي مع ترتيب</small>
        </div>
      </div>
      <div class="collapse mt-2" id="kpiForm">
        <div class="card export-form" style="border-color:#4e1f88">
          <div class="card-header text-white py-1 px-2" style="background:#4e1f88;font-size:.82rem">
            <i class="fas fa-filter ml-1"></i> فلاتر تقرير الأداء
          </div>
          <div class="card-body py-2">
            <form action="{{ route('reports.kpi') }}" method="GET" target="_blank">

              <div class="row mx-0">
                <div class="col-6 pl-1 pr-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">الشهر</label>
                  <select name="month" class="form-control form-control-sm mb-2">
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $mi => $mn)
                      <option value="{{ $mi+1 }}" {{ now()->month == $mi+1 ? 'selected' : '' }}>{{ $mn }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-6 pr-1 pl-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">السنة</label>
                  <input type="number" name="year" class="form-control form-control-sm mb-2"
                    value="{{ now()->year }}">
                </div>
              </div>

              <label class="mb-0" style="font-size:.78rem;color:#555">الموظف</label>
              <select name="employee_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>

              @if(isset($kpiDefs) && $kpiDefs->count())
              <label class="mb-0" style="font-size:.78rem;color:#555">مؤشر محدد</label>
              <select name="kpi_id" class="form-control form-control-sm mb-2">
                <option value="">-- كل المؤشرات --</option>
                @foreach($kpiDefs as $kpi)
                  <option value="{{ $kpi->id }}">{{ $kpi->name }}</option>
                @endforeach
              </select>
              @endif

              <label class="mb-0" style="font-size:.78rem;color:#555">الفئة</label>
              <select name="category" class="form-control form-control-sm mb-2">
                <option value="">-- كل الفئات --</option>
                <option value="performance">أداء</option>
                <option value="quality">جودة</option>
                <option value="attendance">حضور</option>
                <option value="sales">مبيعات</option>
                <option value="custom">مخصص</option>
              </select>

              <label class="mb-0" style="font-size:.78rem;color:#555">
                <i class="fas fa-sort ml-1"></i>ترتيب النتائج
              </label>
              <select name="sort_by" class="form-control form-control-sm mb-3">
                <option value="score_desc">النقاط — الأعلى أولاً</option>
                <option value="score_asc">النقاط — الأقل أولاً</option>
                <option value="achievement_desc">التحقق % — الأعلى أولاً</option>
                <option value="achievement_asc">التحقق % — الأقل أولاً</option>
                <option value="name_asc">الاسم — أ إلى ي</option>
                <option value="name_desc">الاسم — ي إلى أ</option>
              </select>

              <div class="btn-group w-100">
                <button class="btn btn-sm btn-success" name="format" value="excel">
                  <i class="fas fa-file-excel ml-1"></i> Excel
                </button>
                <button class="btn btn-sm btn-danger" name="format" value="pdf">
                  <i class="fas fa-print ml-1"></i> طباعة PDF
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    {{-- ══ الرواتب ══ --}}
    <div class="col-md-6 col-lg-6 mb-4">
      <div class="card report-card" data-toggle="collapse" data-target="#payrollForm" style="border-color:#1a3c6e">
        <div class="card-body">
          <i class="fas fa-money-check-alt" style="color:#1a3c6e"></i>
          <h5 class="mt-2 mb-0">كشف الرواتب</h5>
          <small class="text-muted">Excel / طباعة PDF — تفصيلي بكل مكونات الراتب</small>
        </div>
      </div>
      <div class="collapse mt-2" id="payrollForm">
        <div class="card export-form" style="border-color:#1a3c6e">
          <div class="card-header text-white py-1 px-2" style="background:#1a3c6e;font-size:.82rem">
            <i class="fas fa-filter ml-1"></i> فلاتر تقرير الرواتب
          </div>
          <div class="card-body py-2">
            <form action="{{ route('reports.payroll') }}" method="GET" target="_blank">

              <div class="row mx-0">
                <div class="col-6 pl-1 pr-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">الشهر</label>
                  <select name="month" class="form-control form-control-sm mb-2">
                    <option value="">-- الكل --</option>
                    @foreach(['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'] as $mi => $mn)
                      <option value="{{ $mi+1 }}" {{ now()->month == $mi+1 ? 'selected' : '' }}>{{ $mn }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-6 pr-1 pl-0">
                  <label class="mb-0" style="font-size:.78rem;color:#555">السنة</label>
                  <input type="number" name="year" class="form-control form-control-sm mb-2"
                    value="{{ now()->year }}">
                </div>
              </div>

              <label class="mb-0" style="font-size:.78rem;color:#555">الموظف</label>
              <select name="employee_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الموظفين --</option>
                @foreach($employees as $e)
                  <option value="{{ $e->id }}">{{ $e->employee_name_A }}</option>
                @endforeach
              </select>

              @if(isset($branches) && $branches->count())
              <label class="mb-0" style="font-size:.78rem;color:#555">الفرع</label>
              <select name="branch_id" class="form-control form-control-sm mb-2">
                <option value="">-- جميع الفروع --</option>
                @foreach($branches as $br)
                  <option value="{{ $br->id }}">{{ $br->branch_name }}</option>
                @endforeach
              </select>
              @endif

              <label class="mb-0" style="font-size:.78rem;color:#555">الحالة</label>
              <select name="status" class="form-control form-control-sm mb-2">
                <option value="">-- الكل --</option>
                <option value="1">مسودة</option>
                <option value="2">معتمد</option>
                <option value="3">مدفوع</option>
              </select>

              <label class="mb-0" style="font-size:.78rem;color:#555">
                <i class="fas fa-sort ml-1"></i>ترتيب النتائج
              </label>
              <select name="sort_by" class="form-control form-control-sm mb-3">
                <option value="name_asc">الاسم — أ إلى ي</option>
                <option value="name_desc">الاسم — ي إلى أ</option>
                <option value="net_desc">الراتب الصافي — الأعلى أولاً</option>
                <option value="net_asc">الراتب الصافي — الأقل أولاً</option>
                <option value="gross_desc">الراتب الإجمالي — الأعلى أولاً</option>
                <option value="gross_asc">الراتب الإجمالي — الأقل أولاً</option>
                <option value="month_desc">الشهر — الأحدث أولاً</option>
                <option value="month_asc">الشهر — الأقدم أولاً</option>
              </select>

              <div class="btn-group w-100">
                <button class="btn btn-sm btn-success" name="format" value="excel">
                  <i class="fas fa-file-excel ml-1"></i> Excel
                </button>
                <button class="btn btn-sm btn-danger" name="format" value="pdf">
                  <i class="fas fa-print ml-1"></i> طباعة PDF
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
