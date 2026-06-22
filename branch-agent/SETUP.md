# دليل تركيب LOGG Fingerprint Agent على جهاز الفرع

## متطلبات جهاز الفرع

- Windows 7 أو أحدث
- XAMPP مثبّت (يحتوي على PHP)
- جهاز بصمة ZKTeco متصل بنفس الشبكة المحلية

---

## الخطوة 1 — نسخ ملفات الـ Agent

انسخ مجلد `branch-agent` كاملاً ووضعه في:

```
E:\branch-agent\
```

تأكد من وجود الملفات التالية بعد النسخ:

```
E:\branch-agent\
    agent.php
    config.php          ← مهم (انظر الخطوة 2)
    run.bat
    vendor\             ← مهم (انظر الخطوة 3)
    config.example.php
    composer.json
    SETUP.md
```

---

## الخطوة 2 — إعداد config.php

انسخ ملف الإعدادات:

```
copy E:\branch-agent\config.example.php E:\branch-agent\config.php
```

ثم افتح `config.php` بـ Notepad وعدّل القيم:

```php
return [
    // رابط السيرفر الرئيسي — لا تغيّره
    'server_url' => 'http://26.158.101.62:8080/NEXA/api/fingerprint-agent/push',

    // التوكن — انسخه من صفحة إعدادات جهاز البصمة في البرنامج
    'api_token'  => 'ضع التوكن هنا',

    // IP جهاز البصمة على الشبكة المحلية للفرع
    'device_ip'  => '192.168.1.201',   // ← غيّره حسب IP الجهاز

    // البورت (الافتراضي لـ ZKTeco: 4370)
    'device_port' => 4370,
];
```

---

## الخطوة 3 — تثبيت مكتبات PHP (vendor)

**الطريقة الأسهل:** انسخ مجلد `vendor\` من السيرفر الرئيسي مباشرةً إلى `E:\branch-agent\vendor\`

**أو عبر Composer** (لو مثبّت على الجهاز):

```cmd
cd E:\branch-agent
composer install
```

---

## الخطوة 4 — تعديل run.bat

افتح `E:\branch-agent\run.bat` وتأكد من مسار PHP:

```bat
set PHP_PATH=E:\xampp\php\php.exe
```

لو XAMPP مثبّت في مكان تاني غيّر المسار، مثلاً:
- `C:\xampp\php\php.exe`
- `D:\xampp\php\php.exe`

---

## الخطوة 5 — اختبار يدوي

قبل الجدولة، اختبر الـ agent يدوياً:

1. افتح CMD كـ **Administrator**
2. شغّل:

```cmd
cd E:\branch-agent
"E:\xampp\php\php.exe" -c "E:\xampp\php\php.ini" agent.php
```

يجب أن تظهر رسالة مثل:

```
[2026-06-14 10:00:00] Starting fingerprint sync...
[2026-06-14 10:00:01] Connecting to device 192.168.1.201:4370...
[2026-06-14 10:00:02] Connected. Pulling attendance records...
[2026-06-14 10:00:03] Found 150 record(s) on device.
[2026-06-14 10:00:04] SUCCESS: 12 new log(s) saved. ...
```

---

## الخطوة 6 — جدولة المهمة في Task Scheduler

1. افتح **Task Scheduler** من قائمة Start
2. اختر **Create Task** (مش Basic Task)
3. اعمل الإعدادات التالية:

### تبويب General
- Name: `LOGG Fingerprint Agent`
- تفعيل: **Run whether user is logged on or not**
- تفعيل: **Run with highest privileges**

### تبويب Triggers
- New → Daily → وقت البداية: `01:06 AM`
- تفعيل **Repeat task every**: `30 minutes`
- For a duration of: `1 day`

### تبويب Actions
- Action: **Start a program**
- Program/script: `E:\xampp\php\php.exe`
- Add arguments: `-c "E:\xampp\php\php.ini" "E:\branch-agent\agent.php"`
- Start in: `E:\branch-agent`

### تبويب Settings
- تفعيل: **If the task is already running, do not start a new instance**

4. اضغط OK وأدخل كلمة مرور المستخدم

---

## استكشاف الأخطاء

| المشكلة | السبب | الحل |
|---------|-------|------|
| `Agent failed with code 3` | مسار PHP أو agent خاطئ | تحقق من مسار PHP في run.bat |
| `Agent failed with code -2` | PHP مش لاقي php.ini | أضف `-c "E:\xampp\php\php.ini"` في arguments |
| `config.php not found` | ملف الإعداد ناقص | راجع الخطوة 2 |
| `vendor/autoload.php not found` | مكتبات PHP ناقصة | راجع الخطوة 3 |
| `Cannot connect to device` | IP البصمة غلط أو الجهاز مغلق | تحقق من `device_ip` في config.php |
| `HTTP 401` | التوكن غلط | تحقق من `api_token` في config.php |

---

## ملاحظة مهمة

الـ Task Scheduler لازم يشغّل PHP بـ `-c "E:\xampp\php\php.ini"` صراحةً، وإلا PHP مش هيلاقي الـ extensions ويفشل بـ code -2. هذا لأن Task Scheduler بيشتغل من `C:\Windows\System32` مش من مجلد XAMPP.
