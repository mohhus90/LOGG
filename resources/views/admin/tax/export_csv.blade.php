@extends('admin.layouts.accounting')
@section('title') تصدير لمنظومة الضرائب @endsection
@section('start') الضرائب @endsection
@section('home') <a href="{{ route('tax.index') }}">الضرائب</a> @endsection
@section('startpage') تصدير CSV @endsection

@section('content')
<div class="col-12 col-md-8 offset-md-2">

  <div class="card card-outline card-dark">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-file-csv ml-2"></i>
        تصدير ملفات CSV لمنظومة الضرائب
      </h3>
    </div>
    <div class="card-body">

      <div class="alert alert-info">
        <i class="fas fa-info-circle ml-1"></i>
        يُنتج هذا القسم ملفين بتنسيق <strong>CSV Comma Delimited</strong> جاهزَين للرفع مباشرةً على بوابة منظومة الفاتورة الإلكترونية:
        <ul class="mb-0 mt-1">
          <li><strong>ملف المبيعات (sales-doc)</strong> — يحتوي على فواتير المبيعات (Sent) مُفصّلة بالأصناف</li>
          <li><strong>نموذج 41</strong> — يحتوي على فواتير المشتريات (Received) لتقرير خصم تحت حساب الضريبة</li>
        </ul>
      </div>

      @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
      @endif

      {{-- فلاتر مشتركة --}}
      <div class="row mb-4">
        <div class="col-md-4">
          <label class="font-weight-bold">من تاريخ <span class="text-danger">*</span></label>
          <input type="date" id="fromDate" class="form-control"
            value="{{ now()->startOfMonth()->format('Y-m-d') }}">
        </div>
        <div class="col-md-4">
          <label class="font-weight-bold">إلى تاريخ <span class="text-danger">*</span></label>
          <input type="date" id="toDate" class="form-control"
            value="{{ now()->endOfMonth()->format('Y-m-d') }}">
        </div>
      </div>

      <hr>

      {{-- الكرت الأول: ملف المبيعات --}}
      <div class="card card-outline card-success mb-4">
        <div class="card-header py-2">
          <h5 class="card-title mb-0">
            <i class="fas fa-arrow-up text-success ml-1"></i>
            ملف المبيعات (sales-doc)
          </h5>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            فواتير <strong>المبيعات (Sent)</strong> — صف لكل صنف مع العميل والمبالغ وفئة الضريبة.<br>
            إذا لم يتم سحب تفاصيل الفاتورة من ETA يظهر الصنف كـ "خدمة" بإجمالي الفاتورة.
          </p>
          <div class="d-flex align-items-center">
            <button class="btn btn-success" onclick="downloadCsv('sales-doc')">
              <i class="fas fa-download ml-1"></i>
              تحميل ملف المبيعات CSV
            </button>
            <small class="text-muted mr-3">
              <i class="fas fa-file-alt ml-1"></i> عمود رأس عربي + رمز الحقل + بيانات الفواتير
            </small>
          </div>
        </div>
      </div>

      {{-- الكرت الثاني: نموذج 41 --}}
      <div class="card card-outline card-primary mb-3">
        <div class="card-header py-2">
          <h5 class="card-title mb-0">
            <i class="fas fa-arrow-down text-primary ml-1"></i>
            نموذج 41 (خصم تحت حساب الضريبة)
          </h5>
        </div>
        <div class="card-body">
          <p class="text-muted mb-3">
            فواتير <strong>المشتريات (Received)</strong> — صف لكل فاتورة مع بيانات المورد ونوع الخصم.<br>
            نوع الخصم يُحدَّد تلقائياً من <code>taxTotals → T4</code> في بيانات ETA (افتراضي: 5 = 0.5% خدمات).
          </p>
          <div class="d-flex align-items-center">
            <button class="btn btn-primary" onclick="downloadCsv('form41')">
              <i class="fas fa-download ml-1"></i>
              تحميل نموذج 41 CSV
            </button>
            <small class="text-muted mr-3">
              <i class="fas fa-file-alt ml-1"></i> 15 عمود بتنسيق منظومة الضرائب
            </small>
          </div>
        </div>
      </div>

      {{-- ملاحظات --}}
      <div class="alert alert-warning mt-3">
        <strong><i class="fas fa-exclamation-triangle ml-1"></i>ملاحظات مهمة:</strong>
        <ul class="mb-0 mt-1 small">
          <li>الملفات بترميز <strong>UTF-8 BOM</strong> — مناسب لفتحها في Excel والرفع على المنظومة.</li>
          <li>تأكد من سحب تفاصيل الفواتير أولاً للحصول على بيانات الأصناف الكاملة.</li>
          <li>نوع السلعة <code>COMM_TYPE_S</code>: 1=محلية، 5=أجنبي/تصدير (يُحدد تلقائياً من عنوان العميل).</li>
          <li>نوع التعامل في نموذج 41 <code>TRNS_TYP=4</code> = خدمات مهنية (القيمة الافتراضية).</li>
        </ul>
      </div>

    </div>
  </div>
</div>
@endsection

@section('script')
<script>
function downloadCsv(type) {
    const from = document.getElementById('fromDate').value;
    const to   = document.getElementById('toDate').value;

    if (!from || !to) {
        alert('يرجى تحديد نطاق التاريخ أولاً');
        return;
    }
    if (from > to) {
        alert('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
        return;
    }

    const base = type === 'sales-doc'
        ? '{{ route("tax.export.sales_doc") }}'
        : '{{ route("tax.export.form41") }}';

    window.location.href = base + '?from=' + from + '&to=' + to;
}
</script>
@endsection
