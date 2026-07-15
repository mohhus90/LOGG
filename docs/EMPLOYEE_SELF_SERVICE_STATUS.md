# حالة مشروع "بوابة/تطبيق الموظف" (Employee Self-Service)

> هذا المستند مرجعي دائم داخل المستودع. أي جلسة عمل جديدة (من أي جهاز) يجب أن تبدأ بقراءته لمعرفة ما تم إنجازه وما هو التالي، بنفس أسلوب `docs/ROADMAP_ERP_COMPLETION.md`.
> عند إنجاز أي بند ضع علامة `[x]` وأضف تاريخ الإنجاز.

> **✅ حالة الخطة (2026-07-15): الـ Backend API + بوابة الويب (Blade) + تطبيق الموبايل (Flutter، Android حقيقي) الثلاثة شغّالة ومُختبرة end-to-end فعليًا على جهاز Android حقيقي عبر USB (تسجيل دخول → حضور ببصمة الجهاز الحقيقية + GPS حقيقي → قسيمة راتب → طلب إجازة → مستندات → استقالة).**
> **آخر جلسة عمل (2026-07-15) ركّزت على إصلاح أخطاء حقيقية اكتُشفت أثناء اختبار المستخدم الفعلي على جهازه، وإضافة نظام موافقة على تنزيل الملفات الحساسة. راجع "منجز فعليًا على جهاز حقيقي" تحت لتفاصيل كل إصلاح والسبب الجذري.**

## الطلب الأصلي

تطبيق موبايل + ويب خاص بالموظف لإدارة كل شؤونه: الراتب الشهري، HR letter (شهادة راتب)، طلبات الإجازة، تنزيل ملفاته (مثل شهادة الجيش)، طلب استقالة، بصمة/وجه الحضور والانصراف مع الموقع الجغرافي، تتبع الحركة بتحكم الأدمن في تفعيلها لكل موظف.

## القرارات المعمارية الأساسية

| # | القرار |
|---|---|
| D1 | الموبايل: **Flutter** (كود واحد لـ Android/Web؛ iOS يحتاج جهاز Mac غير متاح). المشروع في `mobile_app/` بجذر المستودع، package name `com.trilogy.nexa.employee_app`. |
| D8 | بيئة تطوير Flutter على Windows تُثبَّت **بدون Android Studio** (تجنبًا لحوار UAC الذي يوقف أي تثبيت آلي عبر winget/MSI للـ JDK) — بدلًا من ذلك: JDK 17 محمول (zip، من Microsoft Build of OpenJDK) + Android `cmdline-tools` + `sdkmanager` لتثبيت `platform-tools`/`platforms;android-34`/`platforms;android-36`/`build-tools;28.0.3`/`build-tools;34.0.0` مباشرةً بدون واجهة رسومية. هذا الإعداد **محلي لكل جهاز** (المسارات مثل `C:\src\flutter` لا تُرفع على الـ repo) — أي جهاز جديد يحتاج نفس الخطوات من الصفر (راجع Phase 2 تحت لملاحظة مهمة عن Git Bash). |
| D2 | التحقق أثناء الحضور بالموبايل: **بصمة/وجه الجهاز نفسه محليًا** (local_auth من جهة العميل)، وليس مطابقة وجه عبر خدمة سحابية. الحقل `attendances.device_verified` تسجيل تدقيقي (audit) فقط وليس تحققًا مشفّرًا من السيرفر. |
| D3 | تتبع الموقع: **لحظي وقت الحضور/الانصراف فقط** (لا تتبع مستمر في الخلفية حاليًا). التفعيل/الإلغاء لكل موظف عبر `employees.location_tracking_enabled` (يتحكم فيه الأدمن من فورم تعديل الموظف). |
| D4 | أمان كلمة المرور: عمود `employees.login_password` كان نصًا صريحًا (plaintext) ومُصمَّم عمدًا لغرض تصدير Excel فقط — **لم يُحذف**. أُضيف عمود جديد `login_password_hash` يُملأ تلقائيًا (Eloquent `saving` event) عند تغيّر `login_password`، والتحقق الحقيقي عند الدخول يتم عبر الـ hash فقط. |
| D5 | بوابة الويب: اكتُشف أثناء التنفيذ وجود بداية بوابة موظف جزئية وغير مكتملة مسبقًا (`app/Http/Controllers/Employee/EmployeePortalController.php` + routes داخل `routes/admin.php` تحت `Route::group(['prefix' => 'employee'])`) — بها **ثغرة تسجيل دخول** (كانت تتحقق من عمود `password` غير موجود أصلاً، فيسقط دائمًا على "رقم الموبايل ككلمة مرور افتراضية"). تم **إصلاحها وتوسيعها** بدل بناء نظام مواز. |
| D6 | حارس مصادقة جديد `employee` (session-based) في `config/auth.php` + `App\Models\Employee` أصبح يمتد من `Illuminate\Foundation\Auth\User` (نفس نمط `Admin`) ليدعم `Auth::guard('employee')`. |
| D7 | حساب التأخير/الأوفرتايم/الخصومات كان محصورًا كمنطق خاص (private methods) داخل `Admin\HR\AttendanceController` — أُستُخرِج إلى `App\Services\AttendanceCalculationService` مشتركة بين شاشة الأدمن وتسجيل حضور الموظف من الـ API، لتفادي ازدواج المنطق. |
| D9 | **موافقة قبل التنزيل**: طلب المستخدم أن الملفات الحساسة تحتاج موافقة أدمن/HR قبل السماح بتنزيلها، بنفس نظام موافقات الإجازات الحالي (`employee_requests`, `EmployeeRequestsController::approve/reject`). النطاق المتفَق عليه: **المستندات الشخصية** (شهادة الجيش، الرقم القومي، إلخ) **وشهادة الراتب (HR Letter)** تحتاج طلب مُسبَّب (سبب مطلوب) وموافقة. **قسائم الراتب الشهرية تبقى فورية بدون موافقة** (موظف بيشوف راتبه بحرية، الحساسية في "خطاب رسمي بيتقدّم لجهة خارجية" وليس رؤية الراتب نفسه). التنفيذ: عمود `employee_requests.document_id` (nullable FK) لطلبات نوع `document_download`، ونوع جديد `salary_certificate` بدون document_id (سبب الطلب في `reason`). حالة الوصول (`none`/`pending`/`approved`) تُحسب من *آخر* طلب لنفس الموظف/المستند وليس تراكميًا — طلب مرفوض يسمح بإعادة الطلب فورًا. |
| D10 | **مشكلة نص عربي معكوس في PDF على أجهزة Android حقيقية (مش في متصفح الديسكتوب)**: اكتُشفت بعد اختبار فعلي على جهاز Android حقيقي — السبب الجذري جزءان: (1) حزمة `printing` الخاصة بعرض PDF داخل التطبيق تستخدم `android.graphics.pdf.PdfRenderer` (محرك النظام الأصلي في Android)، وهو **مختلف** عن محرك Chrome/PDF.js، وله قصور معروف مع bidi العربي المعقّد. جُرِّبت حزمة `pdfx` كبديل ثم اكتُشف أنها **تستخدم نفس `android.graphics.pdf.PdfRenderer` أيضًا** فلا فائدة منها — أُزيلت. (2) السبب الأعمق: قوالب dompdf (`resources/views/pdf/*.blade.php`) اللي بتخلط نص عربي متدفّق مع وسوم `<strong>` متعددة متداخلة (فقرة واحدة فيها 4-5 استبدالات `<strong>`) بتربك محرك bidi البدائي في dompdf بشكل يظهر غلط في محركات عرض PDF الأبسط (زي محرك Android الأصلي) حتى لو ظهر صح في Chrome أو أدوات القراءة المتقدمة. **الحل الفعلي**: إعادة هيكلة `salary_certificate.blade.php` من فقرة نصية متدفقة بها `<strong>` متعددة إلى جدول `<table>` (label/value منفصلين في خلايا مستقلة)، بنفس النمط المُثبَت الناجح أصلاً في `payslip.blade.php` (كان جدول من البداية ولم يُبلَّغ عنه كمُشكِّلة). **الدرس**: أي قالب dompdf عربي جديد يجب أن يستخدم جداول لأي بيانات متغيّرة بدل الجمل المتدفقة بوسوم inline متعددة، وأي اختبار لـ PDF عربي يجب أن يتم على جهاز Android حقيقي (أو محاكي) وليس فقط بمعاينة على الديسكتوب — رغم أن الملف الواحد هو نفسه، اختلاف محرك العرض بيدّي نتيجة مختلفة. |

## الحالة الحالية بالتفصيل

### ✅ منجز — Phase 1: Backend API (`/api/employee/*`, Sanctum bearer token)

- [x] 2026-07-14 مصادقة: `Api\Employee\AuthController` (`login`/`logout`)، `EnsureTokenIsEmployee` middleware يمنع توكن Admin/User من الوصول لمسارات الموظف.
- [x] 2026-07-14 حضور/انصراف: `Api\Employee\AttendanceController` (`today`/`history`/`checkIn`/`checkOut`) — يلتقط GPS، يتحقق اختياريًا من نطاق الفرع الجغرافي (geofence) إن كان مضبوطًا، ويستخدم `AttendanceCalculationService` لحساب التأخير/الأوفرتايم عند الانصراف.
- [x] 2026-07-14 قسائم الراتب: `Api\Employee\PayslipController` (`index`/`show`/`pdf`) — يعرض فقط `MonthlyPayroll` بحالة `status >= 2` (معتمد/مدفوع، وليس مسودة).
- [x] 2026-07-14 شهادة الراتب (HR Letter): `Api\Employee\LetterController@salaryCertificate` — PDF عبر `barryvdh/laravel-dompdf` (تبعية جديدة مثبتة).
- [x] 2026-07-14 المستندات: `Api\Employee\DocumentController` (`index`/`download`) — يعرض `EmployeeDocument` الخاصة بالموظف فقط (يشمل `military_cert` = شهادة الجيش، من النظام الموجود مسبقًا).
- [x] 2026-07-14 طلبات الإجازة: `Api\Employee\LeaveRequestController` (`index`/`store`/`cancel`) — يتحقق من `EmployeeVacationBalance` قبل الإنشاء.
- [x] 2026-07-14 الاستقالة: `Api\Employee\ResignationController` (`store`/`show`) — تُنشئ `EmployeeRequest` بنوع `resignation` (عمود نصي، لا حاجة لتعديل schema). عند موافقة الأدمن من `Admin\HR\EmployeeRequestsController::approve()` (أُضيف فرع `handleResignationApproval`) يتحدّث `employees.resignation_status/resignation_date/resignation_cause/functional_status` تلقائيًا.

**Migrations جديدة:** `employees` (`login_password_hash`, `location_tracking_enabled`)، `attendances` (`check_in_lat/lng`, `check_out_lat/lng`, `source`, `device_verified`)، `branches` (`latitude`, `longitude`, `geofence_radius_m`).

**تعديلات في الأدمن:** فورم تعديل الموظف (`resources/views/admin/employees/_form.blade.php`) فيه الآن checkbox لتفعيل/إلغاء GPS، وفورم الفرع (`resources/views/admin/branches/{create,update}.blade.php`) فيه حقول lat/lng/نطاق السماح الاختيارية.

### ✅ منجز — بوابة الويب (Blade، `/employee/login`)

- [x] 2026-07-14 إصلاح تسجيل الدخول ليستخدم `login_username` + `login_password_hash` عبر `Auth::guard('employee')` بدل الثغرة القديمة.
- [x] 2026-07-14 لوحة تحكم (`employee.dashboard`) — رصيد إجازات، طلب جديد، سجل الطلبات (موجودة أصلاً، فقط أُصلحت).
- [x] 2026-07-14 قسائم الراتب (`employee.payslips`) — عرض + تحميل PDF.
- [x] 2026-07-14 شهادة الراتب (`employee.letters.salary_certificate`) — تحميل PDF مباشر.
- [x] 2026-07-14 المستندات (`employee.documents`) — عرض + تحميل.
- [x] 2026-07-14 الاستقالة (`employee.resignation`) — فورم تقديم + عرض آخر حالة.
- [x] 2026-07-14 سجل الحضور (`employee.attendance`) — عرض فقط (بدون تسجيل حضور من الويب، لأن البصمة/الوجه محلية بالموبايل فقط — القرار D2).
- [x] 2026-07-14 إلغاء طلب إجازة قيد الانتظار (`employee.request.cancel`).
- [x] 2026-07-14 شريط تنقل مشترك بين كل صفحات البوابة (`resources/views/employee/_nav.blade.php`, `_header.blade.php`, `_footer.blade.php`).

**اختُبر فعليًا** (ليس فقط مراجعة كود): تسجيل دخول بجلسة كوكيز حقيقية → حضور/انصراف → تحميل PDF (شهادة راتب) → إرسال طلب إجازة وظهوره في اللوحة → إلغاء الطلب → تقديم استقالة وظهورها → تسجيل خروج ومنع الوصول بعده. تم اكتشاف وإصلاح خطأ فعلي أثناء الاختبار: تاريخ التعيين كان يظهر فارغًا في PDF شهادة الراتب بسبب `optional()->format()` على قيمة نصية وليست Carbon.

## ✅ Phase 2: تطبيق الموبايل (Flutter) — منجز ومُختبر على جهاز Android حقيقي

### إعداد البيئة (2026-07-15)

- [x] Flutter SDK 3.44.6 + JDK 17.0.19 (محمولان، بدون Android Studio) + Android SDK كامل — كل المسارات محلية لهذا الجهاز فقط (`C:\src\flutter`, `C:\src\jdk17`, `C:\Android\sdk`)، أي جهاز جديد يحتاج تكرار نفس الخطوات.
- [x] إضافات VS Code: `Dart-Code.dart-code` و`Dart-Code.flutter`.

**⚠️ ملاحظة Git Bash على Windows**: استدعاء أدوات `.bat` من Android SDK (`sdkmanager.bat`) عبر Git Bash بمسارات فيها `C:\...` قد يُصاب بـ"path mangling" في MSYS (ظهور ملف/مجلد وهمي، فشل تثبيت صامت رغم شريط تقدم "ناجح"). الحل: صدّر `MSYS_NO_PATHCONV=1` قبل أي `.bat`/`.exe`، واستخدم `/` بدل `\` في `--sdk_root` (مثال: `C:/Android/sdk`). تحقّق دائمًا من محتويات المجلد فعليًا بعد أي أمر sdkmanager.

**⚠️ ملاحظة محاكي Android (AVD) على هذا الجهاز**: مساحة القرص كانت ضيقة جدًا (أقل من 8GB متبقية) وفشل تثبيت صورة النظام مرتين بسببها — اتحل بتفريغ مساحة يدويًا. المحاكي نفسه بعدها فشل بـ"Android Emulator hypervisor driver is not installed" لأن Windows Hypervisor Platform مش مفعّل، وده محتاج صلاحيات أدمن + إعادة تشغيل جهاز (مش قابل للأتمتة). **البديل المُستخدم فعليًا وينصح به: جهاز Android حقيقي موصول بكابل USB** — أسرع وأدق (بصمة/GPS حقيقيين). لازم تفعيل "خيارات المطورين" ← "تصحيح أخطاء USB"، ولو الجهاز MIUI/Xiaomi/POCO/Redmi لازم كمان تفعيل "التثبيت عبر USB" بالتحديد (خيار منفصل مقيَّد ببعض الأجهزة، بيرفض التثبيت برسالة `INSTALL_FAILED_USER_RESTRICTED` من غيره).

**⚠️ ملاحظة شبكة**: لو السيرفر المحلي (XAMPP) وجهاز Android مش على نفس الشبكة الفعلية (مثال: جهاز التطوير على شبكة شركة/دومين بجدار حماية بيمنع اتصالات واردة على بورت 80)، استخدم `adb reverse tcp:8080 tcp:80` (بورت 80 نفسه ممنوع الحجز على أندرويد بدون root) ثم شغّل `flutter run --dart-define=SERVER_URL=http://localhost:8080/NEXA` — كده الاتصال بيعدي كامل عبر كابل USB بدون أي حاجة بالشبكة/الفايروول.

### الشاشات والمنطق (2026-07-15) — كلها موجودة في `mobile_app/lib/`

- [x] تسجيل دخول (`screens/login_screen.dart`) — `AuthService` + `ApiClient` (Dio + Sanctum bearer token عبر `flutter_secure_storage`، تجديد/فقدان الجلسة تلقائي عند 401).
- [x] لوحة رئيسية (`dashboard_screen.dart`) — رصيد إجازات، طلبات سابقة، طلب جديد (`new_leave_request_screen.dart`)، إلغاء طلب معلّق.
- [x] حضور/انصراف (`attendance_screen.dart`) — بصمة/وجه الجهاز الحقيقي (`local_auth`) + GPS حقيقي (`geolocator`) قبل كل تسجيل، مربوط بنفس الـ API المُختبر مع الويب.
- [x] الراتب (`payslips_screen.dart`) — قسائم راتب (فورية بدون موافقة) + شهادة راتب/HR letter (تحتاج **طلب مُسبَّب وموافقة** — القرار D9).
- [x] مستنداتي (`documents_screen.dart`) — تحتاج **طلب وصول وموافقة لكل مستند على حدة** قبل ظهور زر التحميل (القرار D9).
- [x] استقالة (`resignation_screen.dart`)، المزيد/تسجيل خروج (`more_screen.dart`).
- [x] عارض PDF داخل التطبيق (`pdf_viewer_screen.dart`, حزمة `printing`) — بدل الاعتماد على أي تطبيق خارجي عشوائي على جهاز المستخدم لعرض الملف.

**اختُبر فعليًا على جهاز Android حقيقي (POCO/Xiaomi، MIUI) موصول بكابل USB — ليس محاكي، وليس مجرد نجاح بناء:**
تسجيل دخول حقيقي → تسجيل حضور ببصمة حقيقية + GPS حقيقي (تحقّق من قاعدة البيانات: `source=mobile_app`, `device_verified=1`, إحداثيات صحيحة) → تصفح قسائم الراتب والمستندات وطلبات الإجازة.

**أخطاء حقيقية اكتُشفت وأُصلحت أثناء هذا الاختبار (مش من مراجعة كود نظرية):**
1. `days_count`/أرصدة الإجازة أعمدة `decimal` في MySQL فبتتحوّل لـ **نص** ("1.0") في JSON من Laravel، ونماذج Dart كانت متوقعة `int` فتنهار وقت الـ parsing — رسالة "خطأ غير متوقع" العامة كانت بتظهر بدل رسالة واضحة. **الدرس**: أي عمود `decimal` من الباك إند لازم يتقرأ في Dart بـ `double.tryParse('$v')`، مش cast مباشر لـ int.
2. نص شهادة الراتب معكوس على جهاز Android حقيقي رغم ظهوره صح على الديسكتوب — راجع القرار **D10** فوق للتفاصيل الكاملة (محرك عرض PDF الأصلي لأندرويد مختلف عن Chrome + قالب dompdf كان بيخلط نص متدفق مع `<strong>` متعددة).
3. `EmployeeVacationBalance` نفس مشكلة الـ decimal-كـ-نص (رقم 1).

### ✅ منجز (2026-07-15) — نظام موافقة على تنزيل الملفات الحساسة (القرار D9)

- [x] Migration: عمود `employee_requests.document_id` (nullable FK → `employee_documents`).
- [x] نوعان جدد لـ `EmployeeRequest.request_type`: `document_download` (له `document_id`) و`salary_certificate` (بدون، السبب في `reason`).
- [x] API: `POST /documents/{id}/request-access`, `POST /letters/salary-certificate/request-access` (سبب مطلوب validation)، `GET /letters/salary-certificate/status`. التنزيل الفعلي (`GET .../download`, `GET .../salary-certificate`) يرجع 403 لو آخر طلب مش `status=1` (موافق عليه).
- [x] بوابة الويب: نفس المنطق بالضبط (`EmployeePortalController`)، صفحة جديدة `employee/salary_certificate.blade.php`، وتحديث `employee/documents.blade.php` لعرض حالة كل مستند (طلب/قيد الانتظار/تحميل).
- [x] الأدمن: نفس شاشة موافقات الإجازات الموجودة (`admin/employee_requests`) بتتعامل مع النوعين الجدد تلقائيًا (بدون شاشة موافقة منفصلة) — أُضيفا لقائمة فلتر النوع فقط.
- [x] الموبايل: `documents_screen.dart` (زر "طلب الوصول" لكل مستند) و`payslips_screen.dart` (دايالوج يطلب السبب لشهادة الراتب).
- **اختُبر فعليًا عبر curl end-to-end**: تنزيل قبل الطلب (403) → طلب وصول (201) → طلب تاني وهو معلّق (422) → موافقة أدمن → تنزيل بعد الموافقة (يعدّي الحاجز، 404 فقط لعدم وجود ملف فعلي في بيانات الاختبار).

## ⏸️ غير منجز — Phase 3: تحسينات مؤجَّلة عمدًا

- [ ] **إشعارات Push (FCM)** عند الموافقة/الرفض على الطلبات وعند اعتماد راتب جديد — طلبها المستخدم صراحة (2026-07-15) لكن أُجِّلت بقراره لأنها تحتاج مشروع Firebase حقيقي (حساب Google) لا يقدر أحد غير المستخدم إنشاءه. **عند الاستئناف**: المستخدم يفتح حساب في Firebase Console، يضيف تطبيق Android بـ package name `com.trilogy.nexa.employee_app`، ينزّل `google-services.json`، ويولّد service account key من Project Settings → Service Accounts، ويشارك الملفين. بعدها التنفيذ: حزمة `firebase_messaging` بالموبايل + endpoint لتسجيل FCM token + استدعاء إرسال إشعار من `EmployeeRequestsController::approve/reject` ومن أي مكان بيغيّر حالة `MonthlyPayroll` لمعتمد/مدفوع.
- [ ] خريطة مواقع الحضور للأدمن + تتبع مستمر في الخلفية (البنية التحتية جاهزة جزئيًا: `attendances.check_in_lat/lng` و`check_out_lat/lng` موجودة بالفعل).
- [ ] مطابقة وجه عبر خدمة سحابية (تم رفضها عمدًا لصالح بصمة الجهاز المحلية — القرار D2).

## ملفات مرجعية سريعة

- API: `routes/api.php` (قسم Employee Self-Service) → `app/Http/Controllers/Api/Employee/*`
- بوابة الويب: `routes/admin.php` (بحث عن `بوابة الموظفين`) → `app/Http/Controllers/Employee/EmployeePortalController.php` → `resources/views/employee/*`
- الموديل: `app/Models/Employee.php` (الآن `extends Authenticatable`, `HasApiTokens`, hash تلقائي لكلمة المرور)
- منطق حساب الحضور المشترك: `app/Services/AttendanceCalculationService.php`
- Migrations الجديدة: ابحث عن التاريخ `2026_07_14` في `database/migrations/`
