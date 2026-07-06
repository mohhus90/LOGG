<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>دليل مؤشرات الأداء (KPIs)</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap');

  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Cairo', 'Arial', sans-serif;
    background: #f0f4f8;
    color: #1a202c;
    font-size: 14px;
    line-height: 1.8;
  }

  /* ─── Print Button (hidden on print) ─── */
  .print-bar {
    background: #2d3748;
    padding: 12px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .print-bar h1 { color: #fff; font-size: 16px; }
  .btn-print {
    background: #48bb78; color: #fff; border: none;
    padding: 8px 24px; border-radius: 6px; cursor: pointer;
    font-family: 'Cairo', sans-serif; font-size: 14px; font-weight: 700;
  }
  .btn-print:hover { background: #38a169; }

  /* ─── Document ─── */
  .document {
    max-width: 900px;
    margin: 30px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,.12);
    overflow: hidden;
  }

  /* ─── Cover ─── */
  .cover {
    background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 60%, #4299e1 100%);
    color: #fff;
    padding: 60px 50px;
    text-align: center;
  }
  .cover .logo { font-size: 48px; margin-bottom: 16px; }
  .cover h1 { font-size: 32px; font-weight: 900; margin-bottom: 8px; }
  .cover h2 { font-size: 18px; font-weight: 400; opacity: .85; }
  .cover .meta {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 40px;
    font-size: 13px;
    opacity: .8;
  }

  /* ─── Body ─── */
  .body { padding: 50px; }

  /* ─── Section ─── */
  .section { margin-bottom: 40px; }
  .section-title {
    font-size: 20px;
    font-weight: 700;
    color: #1a365d;
    border-right: 5px solid #3182ce;
    padding-right: 14px;
    margin-bottom: 18px;
  }
  .section-number {
    display: inline-block;
    background: #ebf8ff;
    color: #2b6cb0;
    border-radius: 50%;
    width: 30px; height: 30px;
    text-align: center; line-height: 30px;
    font-weight: 700;
    margin-left: 8px;
  }

  p { margin-bottom: 12px; color: #2d3748; }

  /* ─── Info Box ─── */
  .info-box {
    background: #ebf8ff;
    border: 1px solid #bee3f8;
    border-radius: 8px;
    padding: 16px 20px;
    margin: 16px 0;
  }
  .info-box.warning {
    background: #fffbeb;
    border-color: #f6e05e;
  }
  .info-box.success {
    background: #f0fff4;
    border-color: #9ae6b4;
  }
  .info-box.danger {
    background: #fff5f5;
    border-color: #fed7d7;
  }
  .info-box .box-title {
    font-weight: 700;
    margin-bottom: 6px;
  }

  /* ─── Table ─── */
  table {
    width: 100%;
    border-collapse: collapse;
    margin: 16px 0;
    font-size: 13px;
  }
  th {
    background: #2b6cb0;
    color: #fff;
    padding: 10px 12px;
    text-align: right;
    font-weight: 600;
  }
  td {
    padding: 9px 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
  }
  tr:nth-child(even) td { background: #f7fafc; }
  tr:hover td { background: #ebf8ff; }

  /* ─── Formula Box ─── */
  .formula {
    background: #1a202c;
    color: #68d391;
    border-radius: 8px;
    padding: 16px 20px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    margin: 16px 0;
    line-height: 2;
    direction: ltr;
    text-align: left;
  }
  .formula .comment { color: #718096; }

  /* ─── Example Card ─── */
  .example-card {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin: 20px 0;
  }
  .example-header {
    background: #2b6cb0;
    color: #fff;
    padding: 12px 20px;
    font-weight: 700;
    font-size: 15px;
  }
  .example-body { padding: 20px; }
  .example-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 14px;
  }
  .step-num {
    background: #3182ce;
    color: #fff;
    min-width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 13px;
    flex-shrink: 0;
    margin-top: 2px;
  }
  .step-content { flex: 1; }
  .step-content strong { color: #1a365d; }

  /* ─── Result Badges ─── */
  .badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
  }
  .badge-blue   { background: #ebf8ff; color: #2b6cb0; }
  .badge-green  { background: #f0fff4; color: #276749; }
  .badge-red    { background: #fff5f5; color: #9b2c2c; }
  .badge-yellow { background: #fffbeb; color: #744210; }
  .badge-gray   { background: #f7fafc; color: #4a5568; }

  /* ─── Achievement Colors ─── */
  .ach-low    { color: #e53e3e; font-weight: 700; }
  .ach-mid    { color: #d69e2e; font-weight: 700; }
  .ach-high   { color: #3182ce; font-weight: 700; }
  .ach-over   { color: #38a169; font-weight: 700; }

  /* ─── Summary Box ─── */
  .summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin: 20px 0;
  }
  .summary-card {
    border-radius: 10px;
    padding: 16px;
    text-align: center;
  }
  .summary-card .value {
    font-size: 24px;
    font-weight: 900;
    margin-bottom: 4px;
  }
  .summary-card .label { font-size: 12px; }
  .card-blue   { background: #ebf8ff; color: #2b6cb0; }
  .card-green  { background: #f0fff4; color: #276749; }
  .card-red    { background: #fff5f5; color: #9b2c2c; }
  .card-purple { background: #faf5ff; color: #553c9a; }

  /* ─── Flow Chart ─── */
  .flow {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    margin: 24px 0;
    flex-wrap: wrap;
  }
  .flow-step {
    background: #2b6cb0;
    color: #fff;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    min-width: 100px;
  }
  .flow-arrow {
    font-size: 22px;
    color: #a0aec0;
    margin: 0 6px;
  }

  /* ─── Divider ─── */
  .divider {
    border: none;
    border-top: 2px dashed #e2e8f0;
    margin: 36px 0;
  }

  /* ─── Page Break ─── */
  .page-break { page-break-before: always; }

  /* ─── Print Styles ─── */
  @media print {
    body { background: #fff; font-size: 12px; }
    .print-bar { display: none !important; }
    .document { box-shadow: none; border-radius: 0; margin: 0; max-width: 100%; }
    .cover { padding: 40px 30px; }
    .body { padding: 30px; }
    .formula { background: #f5f5f5; color: #333; border: 1px solid #ccc; }
    table { font-size: 11px; }
  }

  @page {
    size: A4;
    margin: 15mm;
  }
</style>
</head>
<body>

<!-- Print Bar -->
<div class="print-bar">
  <h1>📊 دليل مؤشرات الأداء (KPIs)</h1>
  <button class="btn-print" onclick="window.print()">🖨️ طباعة / حفظ كـ PDF</button>
</div>

<div class="document">

  <!-- ═══ COVER ═══ -->
  <div class="cover">
    <div class="logo">📊</div>
    <h1>دليل مؤشرات الأداء (KPIs)</h1>
    <h2>Key Performance Indicators — شرح تفصيلي بأمثلة توضيحية</h2>
    <div class="meta">
      <span>📅 {{ now()->format('d / m / Y') }}</span>
      <span>🏢 {{ auth()->guard('admin')->user()->company->com_name ?? 'النظام' }}</span>
      <span>📋 النسخة 1.0</span>
    </div>
  </div>

  <div class="body">

    <!-- ═══ 1. ما هي الـ KPIs ═══ -->
    <div class="section">
      <div class="section-title"><span class="section-number">1</span> ما هي مؤشرات الأداء (KPIs)؟</div>
      <p>
        <strong>مؤشرات الأداء الرئيسية (KPIs)</strong> هي أدوات قياس كمية تُستخدم لتقييم مدى تحقيق الموظف لأهداف محددة مسبقاً خلال فترة زمنية (شهرية في هذا النظام). كل مؤشر له <strong>قيمة مستهدفة</strong>، ويُحسب لكل موظف <strong>قيمة فعلية</strong>، ثم يُستنتج من المقارنة بينهما <strong>نسبة الإنجاز</strong>.
      </p>

      <div class="info-box">
        <div class="box-title">🎯 الهدف من النظام</div>
        <ul style="padding-right: 20px;">
          <li>تقييم أداء كل موظف بشكل موضوعي وعادل</li>
          <li>ربط الأداء بالراتب (مكافآت + خصومات)</li>
          <li>ترتيب الموظفين شهرياً بناءً على الدرجات</li>
          <li>توفير تقارير تفصيلية للإدارة</li>
        </ul>
      </div>

      <!-- Flow Chart -->
      <div class="flow">
        <div class="flow-step">تحديد المؤشرات<br><small>الأهداف والأوزان</small></div>
        <div class="flow-arrow">←</div>
        <div class="flow-step">إدخال القراءات<br><small>القيمة الفعلية شهرياً</small></div>
        <div class="flow-arrow">←</div>
        <div class="flow-step">الحساب التلقائي<br><small>درجة + تأثير مالي</small></div>
        <div class="flow-arrow">←</div>
        <div class="flow-step">الراتب النهائي<br><small>أساسي ± KPI</small></div>
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 2. مكونات المؤشر ═══ -->
    <div class="section">
      <div class="section-title"><span class="section-number">2</span> مكونات كل مؤشر أداء</div>

      <table>
        <thead>
          <tr>
            <th>المكوّن</th>
            <th>الوصف</th>
            <th>مثال</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>الاسم والكود</strong></td>
            <td>تعريف المؤشر وكوده الفريد</td>
            <td>المبيعات الشهرية / SALES_01</td>
          </tr>
          <tr>
            <td><strong>الفئة</strong></td>
            <td>مبيعات، جودة، انضباط، أداء عام، مخصص</td>
            <td>مبيعات</td>
          </tr>
          <tr>
            <td><strong>القيمة المستهدفة</strong></td>
            <td>الرقم الذي يجب على الموظف تحقيقه</td>
            <td>50,000 ج.م</td>
          </tr>
          <tr>
            <td><strong>وحدة القياس</strong></td>
            <td>ج.م، نقطة، %, يوم، رقم</td>
            <td>ج.م</td>
          </tr>
          <tr>
            <td><strong>الوزن النسبي</strong></td>
            <td>أهمية المؤشر من إجمالي 100%</td>
            <td>35%</td>
          </tr>
          <tr>
            <td><strong>التأثير على الراتب</strong></td>
            <td>هل يؤثر هذا المؤشر على الراتب أم للإحصاء فقط؟</td>
            <td>نعم</td>
          </tr>
          <tr>
            <td><strong>نوع التأثير</strong></td>
            <td>مكافأة فقط / خصم فقط / مكافأة أو خصم</td>
            <td>مكافأة أو خصم</td>
          </tr>
          <tr>
            <td><strong>أقصى مكافأة %</strong></td>
            <td>أعلى نسبة مكافأة من الراتب الأساسي</td>
            <td>10%</td>
          </tr>
          <tr>
            <td><strong>أقصى خصم %</strong></td>
            <td>أعلى نسبة خصم من الراتب الأساسي</td>
            <td>5%</td>
          </tr>
        </tbody>
      </table>

      <div class="info-box warning">
        <div class="box-title">⚠️ قاعدة مهمة</div>
        مجموع الأوزان النسبية لجميع المؤشرات يجب أن يساوي <strong>100%</strong> حتى تكون الدرجات الكلية صحيحة.
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 3. معادلات الحساب ═══ -->
    <div class="section page-break">
      <div class="section-title"><span class="section-number">3</span> معادلات الحساب التفصيلية</div>

      <p>يعتمد النظام على <strong>4 معادلات متسلسلة</strong>:</p>

      <!-- 3.1 نسبة الإنجاز -->
      <div class="info-box">
        <div class="box-title">📐 المعادلة 1 — نسبة الإنجاز</div>
      </div>
      <div class="formula">
نسبة الإنجاز (%) = (القيمة الفعلية ÷ القيمة المستهدفة) × 100

<span class="comment">-- مثال: حقق الموظف مبيعات 55,000 من هدف 50,000</span>
نسبة الإنجاز = (55,000 ÷ 50,000) × 100 = <strong style="color:#f6ad55">110%</strong>
      </div>

      <!-- 3.2 الدرجة -->
      <div class="info-box">
        <div class="box-title">📐 المعادلة 2 — درجة المؤشر</div>
      </div>
      <div class="formula">
درجة المؤشر = نسبة الإنجاز × الوزن النسبي ÷ 100

<span class="comment">-- مثال: نسبة إنجاز 110% ووزن المؤشر 35%</span>
الدرجة = 110 × 35 ÷ 100 = <strong style="color:#f6ad55">38.5 نقطة</strong>

<span class="comment">-- الحد الأقصى للدرجة لو حقق 100% = 35 نقطة (مساوية للوزن)</span>
      </div>

      <!-- 3.3 المكافأة -->
      <div class="info-box success">
        <div class="box-title">📐 المعادلة 3 — حساب المكافأة (إنجاز ≥ 100%)</div>
      </div>
      <div class="formula">
<span class="comment">-- الشرط: نسبة الإنجاز ≥ 100% والمؤشر نوعه "مكافأة" أو "مكافأة وخصم"</span>

نسبة المكافأة = MIN( (نسبة الإنجاز - 100) ÷ 100 × أقصى مكافأة% , أقصى مكافأة% )

المبلغ = الراتب الأساسي × نسبة المكافأة ÷ 100

<span class="comment">-- مثال: إنجاز 110%، أقصى مكافأة 10%، راتب 5,000 ج.م</span>
نسبة المكافأة = MIN( (110-100)÷100 × 10 , 10 )
              = MIN( 0.1 × 10 , 10 )
              = MIN( 1% , 10% )
              = <strong style="color:#f6ad55">1%</strong>

المبلغ = 5,000 × 1% = <strong style="color:#68d391">+50 ج.م</strong>
      </div>

      <!-- 3.4 الخصم -->
      <div class="info-box danger">
        <div class="box-title">📐 المعادلة 4 — حساب الخصم (إنجاز &lt; 100%)</div>
      </div>
      <div class="formula">
<span class="comment">-- الشرط: نسبة الإنجاز < 100% والمؤشر نوعه "خصم" أو "مكافأة وخصم"</span>

نسبة الخصم = MIN( (100 - نسبة الإنجاز) ÷ 100 × أقصى خصم% , أقصى خصم% )

المبلغ = الراتب الأساسي × نسبة الخصم ÷ 100

<span class="comment">-- مثال: إنجاز 92.3%، أقصى خصم 10%، راتب 5,000 ج.م</span>
نسبة الخصم = MIN( (100-92.3)÷100 × 10 , 10 )
           = MIN( 0.077 × 10 , 10 )
           = MIN( 0.77% , 10% )
           = <strong style="color:#f6ad55">0.77%</strong>

المبلغ = 5,000 × 0.77% = <strong style="color:#fc8181">-38.5 ج.م</strong>
      </div>

      <div class="info-box">
        <div class="box-title">💡 الفكرة الجوهرية للمعادلة</div>
        <p>النظام لا يعطي المكافأة الكاملة دفعة واحدة، بل بشكل <strong>تدريجي ومتناسب</strong>:</p>
        <ul style="padding-right: 20px;">
          <li>كل 1% زيادة فوق الهدف = جزء من أقصى المكافأة</li>
          <li>كل 1% نقص عن الهدف = جزء من أقصى الخصم</li>
          <li>الحد الأقصى (max_bonus / max_deduction) يحمي الراتب من التغيير الكبير</li>
        </ul>
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 4. مثال شامل ═══ -->
    <div class="section page-break">
      <div class="section-title"><span class="section-number">4</span> مثال شامل — موظف "أحمد محمد"</div>

      <p>راتب أساسي: <strong>5,000 ج.م</strong> — الشهر: <strong>يونيو 2026</strong></p>

      <!-- Setup Table -->
      <p style="font-weight:700; margin-top:16px;">أ) إعداد المؤشرات:</p>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>المؤشر</th>
            <th>الفئة</th>
            <th>الهدف</th>
            <th>الوحدة</th>
            <th>الوزن</th>
            <th>نوع التأثير</th>
            <th>أقصى مكافأة</th>
            <th>أقصى خصم</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>1</td>
            <td>المبيعات الشهرية</td>
            <td><span class="badge badge-blue">مبيعات</span></td>
            <td>50,000</td>
            <td>ج.م</td>
            <td><strong>35%</strong></td>
            <td>مكافأة وخصم</td>
            <td>10%</td>
            <td>5%</td>
          </tr>
          <tr>
            <td>2</td>
            <td>رضا العملاء</td>
            <td><span class="badge badge-green">جودة</span></td>
            <td>90</td>
            <td>نقطة</td>
            <td><strong>25%</strong></td>
            <td>مكافأة فقط</td>
            <td>5%</td>
            <td>—</td>
          </tr>
          <tr>
            <td>3</td>
            <td>الانضباط والحضور</td>
            <td><span class="badge badge-yellow">انضباط</span></td>
            <td>26</td>
            <td>يوم</td>
            <td><strong>20%</strong></td>
            <td>مكافأة وخصم</td>
            <td>5%</td>
            <td>10%</td>
          </tr>
          <tr>
            <td>4</td>
            <td>معدل الأخطاء</td>
            <td><span class="badge badge-gray">جودة</span></td>
            <td>2</td>
            <td>%</td>
            <td><strong>20%</strong></td>
            <td>مكافأة وخصم</td>
            <td>5%</td>
            <td>5%</td>
          </tr>
          <tr style="background:#f0fff4;">
            <td colspan="5"><strong>إجمالي الأوزان</strong></td>
            <td><strong>100%</strong> ✅</td>
            <td colspan="3"></td>
          </tr>
        </tbody>
      </table>

      <!-- Results Table -->
      <p style="font-weight:700; margin-top:24px;">ب) نتائج شهر يونيو:</p>
      <table>
        <thead>
          <tr>
            <th>المؤشر</th>
            <th>الهدف</th>
            <th>الفعلي</th>
            <th>نسبة الإنجاز</th>
            <th>الدرجة</th>
            <th>التأثير المالي</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>المبيعات الشهرية</td>
            <td>50,000</td>
            <td>55,000</td>
            <td><span class="ach-over">110%</span></td>
            <td>38.5</td>
            <td><span style="color:#38a169;font-weight:700;">+50 ج.م</span> (مكافأة)</td>
          </tr>
          <tr>
            <td>رضا العملاء</td>
            <td>90</td>
            <td>85</td>
            <td><span class="ach-high">94.4%</span></td>
            <td>23.6</td>
            <td><span style="color:#718096;">0 ج.م</span> (مكافأة فقط → لم يصل 100%)</td>
          </tr>
          <tr>
            <td>الانضباط والحضور</td>
            <td>26</td>
            <td>24</td>
            <td><span class="ach-mid">92.3%</span></td>
            <td>18.5</td>
            <td><span style="color:#e53e3e;font-weight:700;">−38.5 ج.م</span> (خصم)</td>
          </tr>
          <tr>
            <td>معدل الأخطاء</td>
            <td>2%</td>
            <td>1.5%</td>
            <td><span class="ach-over">133%</span></td>
            <td>26.6</td>
            <td><span style="color:#38a169;font-weight:700;">+37.5 ج.م</span> (مكافأة)</td>
          </tr>
          <tr style="background:#ebf8ff; font-weight:700;">
            <td colspan="4"><strong>الإجمالي</strong></td>
            <td><strong>107.2 / 100</strong></td>
            <td></td>
          </tr>
        </tbody>
      </table>

      <!-- Summary Cards -->
      <p style="font-weight:700; margin-top:24px;">ج) ملخص الراتب:</p>
      <div class="summary-grid">
        <div class="summary-card card-blue">
          <div class="value">5,000</div>
          <div class="label">الراتب الأساسي ج.م</div>
        </div>
        <div class="summary-card card-green">
          <div class="value">+87.5</div>
          <div class="label">إجمالي المكافآت ج.م</div>
        </div>
        <div class="summary-card card-red">
          <div class="value">−38.5</div>
          <div class="label">إجمالي الخصومات ج.م</div>
        </div>
        <div class="summary-card card-purple">
          <div class="value">5,049</div>
          <div class="label">الراتب النهائي ج.م</div>
        </div>
      </div>

      <div class="formula">
الراتب النهائي = 5,000 + (+50 + 0 + 0 + 37.5) − 38.5
              = 5,000 + 87.5 − 38.5
              = <strong style="color:#f6ad55">5,049 ج.م</strong>
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 5. سلم ألوان الأداء ═══ -->
    <div class="section">
      <div class="section-title"><span class="section-number">5</span> سلّم ألوان تقييم الأداء</div>

      <table>
        <thead>
          <tr>
            <th>نسبة الإنجاز</th>
            <th>التقدير</th>
            <th>اللون</th>
            <th>معنى التأثير</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="ach-low">أقل من 60%</span></td>
            <td>ضعيف</td>
            <td>🔴 أحمر</td>
            <td>خصم كبير إذا كان المؤشر يؤثر على الراتب</td>
          </tr>
          <tr>
            <td><span class="ach-mid">60% — 79%</span></td>
            <td>يحتاج تحسين</td>
            <td>🟡 أصفر</td>
            <td>خصم متوسط</td>
          </tr>
          <tr>
            <td><span class="ach-high">80% — 99%</span></td>
            <td>جيد</td>
            <td>🔵 أزرق</td>
            <td>خصم بسيط أو لا خصم حسب نوع المؤشر</td>
          </tr>
          <tr>
            <td><span class="ach-over">100% فأكثر</span></td>
            <td>ممتاز</td>
            <td>🟢 أخضر</td>
            <td>مكافأة تتزايد كلما زاد الإنجاز (حتى الحد الأقصى)</td>
          </tr>
        </tbody>
      </table>
    </div>

    <hr class="divider">

    <!-- ═══ 6. أنواع المؤشرات ═══ -->
    <div class="section page-break">
      <div class="section-title"><span class="section-number">6</span> أنواع المؤشرات وطريقة التعامل معها</div>

      <table>
        <thead>
          <tr>
            <th>نوع التأثير</th>
            <th>شرط المكافأة</th>
            <th>شرط الخصم</th>
            <th>متى تُستخدم؟</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="badge badge-green">مكافأة فقط</span></td>
            <td>إنجاز ≥ 100%</td>
            <td>لا يوجد</td>
            <td>مؤشرات التفوق مثل عدد العملاء الجدد</td>
          </tr>
          <tr>
            <td><span class="badge badge-red">خصم فقط</span></td>
            <td>لا يوجد</td>
            <td>إنجاز &lt; 100%</td>
            <td>الحضور الإلزامي، الالتزام بالأنظمة</td>
          </tr>
          <tr>
            <td><span class="badge badge-yellow">مكافأة وخصم</span></td>
            <td>إنجاز ≥ 100%</td>
            <td>إنجاز &lt; 100%</td>
            <td>المبيعات، الإنتاجية (الأكثر استخداماً)</td>
          </tr>
          <tr>
            <td><span class="badge badge-gray">للإحصاء فقط</span></td>
            <td>—</td>
            <td>—</td>
            <td>مؤشرات المراقبة والتحليل دون تأثير مالي</td>
          </tr>
        </tbody>
      </table>

      <div class="info-box warning">
        <div class="box-title">⚠️ ملاحظة على مؤشرات "اللعكس"</div>
        <p>
          بعض المؤشرات يكون الهدف منها التخفيض لا الزيادة (مثل <strong>معدل الأخطاء: الهدف 2%</strong>).
          إذا حقق الموظف 1.5% فهو أفضل من الهدف (إنجاز 133%).
          لكن إذا وصل لـ 4% فإنجازه 200% وهذا أسوأ!
          في هذه الحالة ينصح بتعيين المؤشر <strong>للإحصاء فقط</strong> وتتبعه يدوياً،
          أو تحويله لمؤشر مقلوب مثل "نسبة الدقة = 100% - معدل الأخطاء".
        </p>
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 7. مثال الحد الأقصى ═══ -->
    <div class="section">
      <div class="section-title"><span class="section-number">7</span> الحد الأقصى للمكافأة والخصم</div>

      <p>الحد الأقصى يحمي الراتب من التذبذب الكبير:</p>

      <div class="example-card">
        <div class="example-header">📌 مثال: مؤشر المبيعات — أقصى مكافأة 10%، راتب 5,000 ج.م</div>
        <div class="example-body">
          <table>
            <thead>
              <tr><th>الفعلي</th><th>نسبة الإنجاز</th><th>الزيادة فوق الهدف</th><th>نسبة المكافأة المحسوبة</th><th>المكافأة الفعلية</th></tr>
            </thead>
            <tbody>
              <tr><td>50,000</td><td>100%</td><td>0%</td><td>0%</td><td>0 ج.م</td></tr>
              <tr><td>55,000</td><td>110%</td><td>10%</td><td>MIN(1%, 10%) = 1%</td><td><span style="color:#38a169;font-weight:700;">50 ج.م</span></td></tr>
              <tr><td>75,000</td><td>150%</td><td>50%</td><td>MIN(5%, 10%) = 5%</td><td><span style="color:#38a169;font-weight:700;">250 ج.م</span></td></tr>
              <tr><td>100,000</td><td>200%</td><td>100%</td><td>MIN(10%, 10%) = 10%</td><td><span style="color:#38a169;font-weight:700;">500 ج.م</span></td></tr>
              <tr><td>200,000</td><td>400%</td><td>300%</td><td>MIN(30%, 10%) = <strong>10% ← الحد</strong></td><td><span style="color:#38a169;font-weight:700;">500 ج.م (لا يزيد)</span></td></tr>
            </tbody>
          </table>
          <div class="info-box success" style="margin-top:12px;">
            <strong>بعد 200% إنجاز، المكافأة تثبت عند 500 ج.م (10% من الراتب) ولا تزيد مهما ارتفعت المبيعات.</strong>
          </div>
        </div>
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 8. الخطوات العملية ═══ -->
    <div class="section page-break">
      <div class="section-title"><span class="section-number">8</span> الخطوات العملية لاستخدام النظام</div>

      <div class="example-card">
        <div class="example-header">🚀 الخطوة 1 — تعريف المؤشرات (مرة واحدة فقط)</div>
        <div class="example-body">
          <div class="example-step">
            <div class="step-num">1</div>
            <div class="step-content">اذهب إلى: <strong>مؤشرات الأداء ← تعريفات المؤشرات</strong></div>
          </div>
          <div class="example-step">
            <div class="step-num">2</div>
            <div class="step-content">اضغط <strong>"إضافة مؤشر جديد"</strong> وأدخل: الاسم، الكود، الفئة، الهدف، الوزن</div>
          </div>
          <div class="example-step">
            <div class="step-num">3</div>
            <div class="step-content">حدد هل يؤثر على الراتب ونوع التأثير والحدود القصوى</div>
          </div>
          <div class="example-step">
            <div class="step-num">4</div>
            <div class="step-content">تأكد من أن مجموع الأوزان = <strong>100%</strong></div>
          </div>
        </div>
      </div>

      <div class="example-card" style="margin-top:16px;">
        <div class="example-header">📝 الخطوة 2 — إدخال القراءات الشهرية (كل شهر)</div>
        <div class="example-body">
          <div class="example-step">
            <div class="step-num">1</div>
            <div class="step-content">اذهب إلى: <strong>مؤشرات الأداء ← القراءات الشهرية</strong></div>
          </div>
          <div class="example-step">
            <div class="step-num">2</div>
            <div class="step-content">اختر الشهر والسنة المطلوبين</div>
          </div>
          <div class="example-step">
            <div class="step-num">3</div>
            <div class="step-content">ادخل القيمة الفعلية لكل موظف في كل مؤشر — الحساب تلقائي</div>
          </div>
          <div class="example-step">
            <div class="step-num">4</div>
            <div class="step-content">أو حمّل <strong>شيت Excel</strong> من الزر المخصص، أدخل القراءات، ثم استورد الشيت</div>
          </div>
          <div class="example-step">
            <div class="step-num">5</div>
            <div class="step-content">اضغط <strong>"حفظ التقييمات"</strong></div>
          </div>
        </div>
      </div>

      <div class="example-card" style="margin-top:16px;">
        <div class="example-header">📊 الخطوة 3 — مراجعة التقارير</div>
        <div class="example-body">
          <div class="example-step">
            <div class="step-num">1</div>
            <div class="step-content">اذهب إلى: <strong>مؤشرات الأداء ← تقرير الأداء</strong></div>
          </div>
          <div class="example-step">
            <div class="step-num">2</div>
            <div class="step-content">شاهد <strong>ترتيب الموظفين</strong> مع الميداليات (ذهب/فضة/برونز)</div>
          </div>
          <div class="example-step">
            <div class="step-num">3</div>
            <div class="step-content">راجع المكافآت والخصومات قبل صرف الرواتب</div>
          </div>
          <div class="example-step">
            <div class="step-num">4</div>
            <div class="step-content">عند احتساب الراتب، سيُضاف KPI تلقائياً في <strong>كشف الرواتب</strong></div>
          </div>
        </div>
      </div>
    </div>

    <hr class="divider">

    <!-- ═══ 9. أسئلة شائعة ═══ -->
    <div class="section">
      <div class="section-title"><span class="section-number">9</span> أسئلة شائعة</div>

      <div class="info-box">
        <div class="box-title">❓ ماذا لو لم يُدخل الهدف لموظف معين في شهر ما؟</div>
        لن يُحسب له أي تأثير مالي لذلك المؤشر في ذلك الشهر. فقط الخلايا المُدخلة تُحسب.
      </div>

      <div class="info-box">
        <div class="box-title">❓ هل يمكن أن يكون لديّ مكافأة وخصم في نفس الشهر؟</div>
        نعم — كل مؤشر يُحسب باستقلالية. يمكنك الحصول على مكافأة من مؤشر المبيعات وخصم من مؤشر الحضور في نفس الوقت.
      </div>

      <div class="info-box">
        <div class="box-title">❓ هل المؤشرات المحددة "للإحصاء فقط" تؤثر على الدرجة الإجمالية؟</div>
        نعم — تؤثر على الدرجة والترتيب في التقرير، لكن لا تُضاف أي مبالغ لكشف الرواتب.
      </div>

      <div class="info-box">
        <div class="box-title">❓ ماذا لو كان الهدف صفر أو لم يُحدد؟</div>
        النظام يعامل الهدف الصفري كـ 1 تلقائياً لتجنب القسمة على صفر.
      </div>

      <div class="info-box">
        <div class="box-title">❓ كيف يُضاف مبلغ KPI في الراتب بالضبط؟</div>
        في كشف الرواتب: <br>
        <code>الراتب الصافي = الأساسي + البدلات + الأوفرتايم + العمولات + مكافآت KPI − خصومات KPI</code>
      </div>
    </div>

    <!-- ═══ Footer ═══ -->
    <hr class="divider">
    <div style="text-align:center; color:#a0aec0; font-size:12px; padding-top:10px;">
      تم إنشاء هذا الدليل بتاريخ {{ now()->format('d/m/Y') }} — نظام NEXA لإدارة الموارد البشرية
    </div>

  </div><!-- /.body -->
</div><!-- /.document -->

</body>
</html>
