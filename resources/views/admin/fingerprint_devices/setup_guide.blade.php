<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>دليل إعداد Agent الفرع — {{ $device->device_name }}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
    font-size: 13px;
    color: #1a1a1a;
    background: #fff;
    direction: rtl;
    padding: 30px 40px;
  }

  /* ── شريط الأدوات (يختفي عند الطباعة) ── */
  .toolbar {
    background: #6f42c1;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .toolbar button {
    background: #fff;
    color: #6f42c1;
    border: none;
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    margin-right: auto;
  }
  .toolbar button:hover { background: #f0e6ff; }

  /* ── رأس الوثيقة ── */
  .doc-header {
    border-bottom: 3px solid #6f42c1;
    padding-bottom: 16px;
    margin-bottom: 24px;
  }
  .doc-header h1 {
    font-size: 20px;
    color: #6f42c1;
    margin-bottom: 4px;
  }
  .doc-header .meta {
    font-size: 11px;
    color: #666;
    display: flex;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 8px;
  }
  .doc-header .meta span strong { color: #333; }

  /* ── بطاقة التوكن ── */
  .token-box {
    background: #f3e8ff;
    border: 2px solid #c084fc;
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 20px;
  }
  .token-box h3 { font-size: 13px; color: #6f42c1; margin-bottom: 8px; }
  .token-value {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    background: #fff;
    border: 1px solid #c084fc;
    border-radius: 4px;
    padding: 8px 12px;
    word-break: break-all;
    letter-spacing: 0.5px;
    direction: ltr;
    text-align: left;
    margin-bottom: 6px;
  }
  .token-box .url-val {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    background: #fff;
    border: 1px solid #c084fc;
    border-radius: 4px;
    padding: 6px 12px;
    word-break: break-all;
    direction: ltr;
    text-align: left;
  }
  .token-warning {
    font-size: 11px;
    color: #dc3545;
    margin-top: 6px;
  }

  /* ── الخطوات ── */
  .steps-title {
    font-size: 15px;
    font-weight: 700;
    color: #333;
    border-right: 4px solid #6f42c1;
    padding-right: 10px;
    margin-bottom: 16px;
  }

  .step {
    display: flex;
    gap: 14px;
    margin-bottom: 18px;
    align-items: flex-start;
  }
  .step-num {
    min-width: 32px;
    height: 32px;
    background: #6f42c1;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
  }
  .step-body h4 { font-size: 13px; font-weight: 700; margin-bottom: 4px; color: #333; }
  .step-body p  { font-size: 12px; color: #555; margin-bottom: 4px; line-height: 1.6; }
  .step-body code {
    font-family: 'Courier New', monospace;
    background: #f4f4f4;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 11px;
    direction: ltr;
    unicode-bidi: embed;
  }
  .step-body .cmd {
    display: block;
    font-family: 'Courier New', monospace;
    font-size: 11px;
    background: #1e1e1e;
    color: #d4d4d4;
    border-radius: 4px;
    padding: 8px 12px;
    margin: 6px 0;
    direction: ltr;
    text-align: left;
  }

  /* ── جدول إعدادات config.php ── */
  .config-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 11.5px;
  }
  .config-table th {
    background: #6f42c1;
    color: #fff;
    padding: 6px 10px;
    text-align: right;
    font-weight: 600;
  }
  .config-table td {
    padding: 6px 10px;
    border-bottom: 1px solid #e8e8e8;
    vertical-align: top;
  }
  .config-table tr:nth-child(even) td { background: #faf5ff; }
  .config-table .key {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    color: #6f42c1;
    direction: ltr;
    unicode-bidi: embed;
  }
  .config-table .val {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    word-break: break-all;
    direction: ltr;
    unicode-bidi: embed;
  }

  /* ── جدول الأخطاء ── */
  .error-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 11.5px;
  }
  .error-table th {
    background: #dc3545;
    color: #fff;
    padding: 6px 10px;
    text-align: right;
  }
  .error-table td {
    padding: 6px 10px;
    border-bottom: 1px solid #f5c6cb;
    vertical-align: top;
  }
  .error-table tr:nth-child(even) td { background: #fff5f5; }

  /* ── الـ Task Scheduler ── */
  .task-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 12px 16px;
    margin-top: 8px;
  }
  .task-row {
    display: flex;
    gap: 8px;
    margin-bottom: 6px;
    font-size: 12px;
    align-items: center;
  }
  .task-row .label {
    min-width: 140px;
    font-weight: 600;
    color: #555;
  }
  .task-row .value {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    background: #fff;
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 2px 8px;
    direction: ltr;
    unicode-bidi: embed;
  }

  /* ── تذييل ── */
  .doc-footer {
    margin-top: 30px;
    border-top: 1px solid #dee2e6;
    padding-top: 12px;
    font-size: 11px;
    color: #888;
    display: flex;
    justify-content: space-between;
  }

  /* ── طباعة ── */
  @media print {
    .toolbar { display: none !important; }
    body { padding: 10px 20px; }
    .step-num { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .config-table th, .error-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .token-box { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>

{{-- شريط الأدوات --}}
<div class="toolbar">
  <span style="font-size:18px">&#128196;</span>
  <div>
    <strong>دليل إعداد Agent الفرع</strong>
    <div style="font-size:11px;opacity:.85">اضغط "طباعة / حفظ PDF" لتصدير هذا الدليل</div>
  </div>
  <button onclick="window.print()">&#128424; طباعة / حفظ PDF</button>
</div>

{{-- رأس الوثيقة --}}
<div class="doc-header">
  <h1>&#128196; دليل إعداد LOGG Fingerprint Agent</h1>
  <div style="font-size:12px;color:#555;margin-top:4px">للتركيب على كمبيوتر الفرع الخاص بجهاز البصمة</div>
  <div class="meta">
    <span><strong>الجهاز:</strong> {{ $device->device_name }}</span>
    <span><strong>كود الجهاز:</strong> {{ $device->device_code }}</span>
    @if($device->location)
    <span><strong>الموقع:</strong> {{ $device->location }}</span>
    @endif
    <span><strong>تاريخ الإصدار:</strong> {{ now()->format('Y-m-d') }}</span>
  </div>
</div>

{{-- بطاقة التوكن --}}
@if($device->api_token)
<div class="token-box">
  <h3>&#128273; بيانات الاتصال بالسيرفر — ضعها في config.php</h3>
  <table style="width:100%;font-size:12px;margin-top:4px">
    <tr>
      <td style="padding:4px 0;width:120px;font-weight:700;color:#555">API Token:</td>
      <td><div class="token-value">{{ $device->api_token }}</div></td>
    </tr>
    <tr>
      <td style="padding:4px 0;font-weight:700;color:#555">Server URL:</td>
      <td><div class="url-val">{{ $serverUrl }}</div></td>
    </tr>
  </table>
  <div class="token-warning">&#9888; احتفظ بهذا التوكن في مكان آمن — لا تشاركه مع أحد — خاص بهذا الجهاز فقط</div>
</div>
@endif

{{-- خطوات التركيب --}}
<div class="steps-title">خطوات التركيب</div>

<div class="step">
  <div class="step-num">1</div>
  <div class="step-body">
    <h4>نسخ ملفات الـ Agent على الجهاز</h4>
    <p>انسخ مجلد <code>branch-agent</code> من السيرفر الرئيسي وضعه في:</p>
    <span class="cmd">E:\branch-agent\</span>
    <p>تأكد من وجود الملفات: <code>agent.php</code> &nbsp;|&nbsp; <code>composer.json</code> &nbsp;|&nbsp; <code>config.example.php</code> &nbsp;|&nbsp; <code>run.bat</code></p>
  </div>
</div>

<div class="step">
  <div class="step-num">2</div>
  <div class="step-body">
    <h4>إنشاء ملف config.php</h4>
    <p>افتح CMD وشغّل:</p>
    <span class="cmd">copy E:\branch-agent\config.example.php E:\branch-agent\config.php</span>
    <p>ثم افتح <code>config.php</code> بـ Notepad وعدّل القيم التالية:</p>
    <table class="config-table">
      <tr>
        <th style="width:35%">المفتاح</th>
        <th style="width:40%">القيمة</th>
        <th>ملاحظة</th>
      </tr>
      <tr>
        <td class="key">'server_url'</td>
        <td class="val">{{ $serverUrl }}</td>
        <td>لا تغيّره</td>
      </tr>
      <tr>
        <td class="key">'api_token'</td>
        <td class="val">{{ $device->api_token ?? 'ضع التوكن من الأعلى' }}</td>
        <td>من البطاقة أعلاه</td>
      </tr>
      <tr>
        <td class="key">'device_ip'</td>
        <td class="val">192.168.X.X</td>
        <td>IP جهاز البصمة على الشبكة المحلية</td>
      </tr>
      <tr>
        <td class="key">'device_port'</td>
        <td class="val">4370</td>
        <td>البورت الافتراضي لـ ZKTeco</td>
      </tr>
    </table>
  </div>
</div>

<div class="step">
  <div class="step-num">3</div>
  <div class="step-body">
    <h4>تثبيت مكتبات PHP (مرة واحدة فقط)</h4>
    <p><strong>الطريقة الأسهل:</strong> انسخ مجلد <code>vendor\</code> من السيرفر الرئيسي مباشرةً إلى <code>E:\branch-agent\vendor\</code></p>
    <p><strong>أو عبر Composer</strong> (إذا كان مثبّتاً على الجهاز):</p>
    <span class="cmd">cd E:\branch-agent
composer install</span>
  </div>
</div>

<div class="step">
  <div class="step-num">4</div>
  <div class="step-body">
    <h4>التأكد من مسار PHP في run.bat</h4>
    <p>افتح <code>E:\branch-agent\run.bat</code> وتأكد أن السطر الأول يحتوي المسار الصحيح لـ PHP:</p>
    <span class="cmd">set PHP_PATH=E:\xampp\php\php.exe</span>
    <p>غيّر حرف القرص إذا كان XAMPP مثبّتاً في مكان آخر (مثل <code>C:\xampp</code> أو <code>D:\xampp</code>).</p>
  </div>
</div>

<div class="step">
  <div class="step-num">5</div>
  <div class="step-body">
    <h4>اختبار يدوي قبل الجدولة</h4>
    <p>افتح CMD كـ <strong>Administrator</strong> وشغّل:</p>
    <span class="cmd">"E:\xampp\php\php.exe" -c "E:\xampp\php\php.ini" "E:\branch-agent\agent.php"</span>
    <p>يجب أن تظهر رسالة نجاح مثل: <code>SUCCESS: X new log(s) saved.</code></p>
  </div>
</div>

<div class="step">
  <div class="step-num">6</div>
  <div class="step-body">
    <h4>جدولة المهمة في Windows Task Scheduler</h4>
    <p>افتح <strong>Task Scheduler</strong> ← <strong>Create Task</strong> (ليس Basic Task) وأدخل الإعدادات التالية:</p>
    <div class="task-box">
      <div style="font-weight:700;color:#555;margin-bottom:8px">تبويب General</div>
      <div class="task-row"><span class="label">Name:</span><span class="value">LOGG Fingerprint Agent</span></div>
      <div class="task-row"><span class="label">Run as:</span><span style="font-size:12px">تفعيل "Run whether user is logged on or not" + "Run with highest privileges"</span></div>

      <div style="font-weight:700;color:#555;margin:10px 0 8px">تبويب Triggers</div>
      <div class="task-row"><span class="label">Begin the task:</span><span class="value">On a schedule — Daily</span></div>
      <div class="task-row"><span class="label">Start time:</span><span class="value">01:06 AM</span></div>
      <div class="task-row"><span class="label">Repeat every:</span><span class="value">30 minutes — for 1 day</span></div>

      <div style="font-weight:700;color:#555;margin:10px 0 8px">تبويب Actions</div>
      <div class="task-row"><span class="label">Program/script:</span><span class="value">E:\xampp\php\php.exe</span></div>
      <div class="task-row"><span class="label">Add arguments:</span><span class="value">-c "E:\xampp\php\php.ini" "E:\branch-agent\agent.php"</span></div>
      <div class="task-row"><span class="label">Start in:</span><span class="value">E:\branch-agent</span></div>

      <div style="font-weight:700;color:#555;margin:10px 0 8px">تبويب Settings</div>
      <div class="task-row"><span class="label">If already running:</span><span class="value">Do not start a new instance</span></div>
    </div>
    <p style="margin-top:8px;color:#dc3545;font-size:11px">&#9888; <strong>مهم:</strong> لا تستخدم run.bat في الجدولة — استخدم php.exe مباشرةً مع <code>-c</code> لتجنب خطأ code -2</p>
  </div>
</div>

{{-- جدول الأخطاء --}}
<div class="steps-title" style="margin-top:24px">استكشاف الأخطاء الشائعة</div>
<table class="error-table">
  <tr>
    <th style="width:30%">رسالة الخطأ</th>
    <th style="width:30%">السبب</th>
    <th>الحل</th>
  </tr>
  <tr>
    <td>Agent failed with code 3</td>
    <td>مسار PHP أو agent.php غلط</td>
    <td>تحقق من مسار PHP في run.bat أو Arguments في Task Scheduler</td>
  </tr>
  <tr>
    <td>Agent failed with code -2</td>
    <td>PHP لا يجد php.ini (working directory خاطئ)</td>
    <td>أضف <code>-c "E:\xampp\php\php.ini"</code> في Arguments</td>
  </tr>
  <tr>
    <td>config.php not found</td>
    <td>ملف الإعداد غير موجود</td>
    <td>راجع الخطوة 2 — انسخ config.example.php إلى config.php</td>
  </tr>
  <tr>
    <td>vendor/autoload.php not found</td>
    <td>مكتبات PHP غير مثبّتة</td>
    <td>راجع الخطوة 3 — انسخ مجلد vendor أو شغّل composer install</td>
  </tr>
  <tr>
    <td>Cannot connect to device</td>
    <td>IP البصمة غلط أو الجهاز مغلق</td>
    <td>تحقق من device_ip في config.php وأن الجهاز شغّال</td>
  </tr>
  <tr>
    <td>HTTP 401</td>
    <td>التوكن غلط أو منتهي</td>
    <td>تحقق من api_token في config.php — جدّد التوكن من البرنامج إذا لزم</td>
  </tr>
</table>

{{-- تذييل --}}
<div class="doc-footer">
  <span>LOGG — نظام إدارة الموارد البشرية</span>
  <span>الجهاز: {{ $device->device_name }} — {{ $device->device_code }}</span>
  <span>{{ now()->format('Y-m-d') }}</span>
</div>

</body>
</html>
