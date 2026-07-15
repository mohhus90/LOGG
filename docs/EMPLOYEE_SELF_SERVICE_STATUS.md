# حالة مشروع "بوابة/تطبيق الموظف" (Employee Self-Service)

> هذا المستند مرجعي دائم داخل المستودع. أي جلسة عمل جديدة (من أي جهاز) يجب أن تبدأ بقراءته لمعرفة ما تم إنجازه وما هو التالي، بنفس أسلوب `docs/ROADMAP_ERP_COMPLETION.md`.
> عند إنجاز أي بند ضع علامة `[x]` وأضف تاريخ الإنجاز.

> **✅ حالة الخطة (2026-07-15): الـ Backend API + بوابة الويب (Blade) مكتملان ومُختبران end-to-end فعليًا (تسجيل دخول → حضور/انصراف → قسيمة راتب → مستندات → طلب إجازة → استقالة → إلغاء → تسجيل خروج، عبر HTTP حقيقي بجلسة كوكيز).**
> **🚧 تطبيق الموبايل (Flutter) بدأ فعليًا** — تم تجهيز بيئة التطوير بالكامل (Flutter SDK + Android SDK + JDK محمول، بدون Android Studio) وتم إنشاء مشروع Flutter فارغ (`mobile_app/`) وتم التحقق من أن كامل السلسلة (toolchain) تعمل ببناء تجريبي ناجح فعليًا. **لم تُكتب أي شاشة أو منطق للتطبيق نفسه بعد** — هذا هو نقطة الاستئناف التالية (راجع Phase 2 تحت).

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

## 🚧 قيد التنفيذ — Phase 2: تطبيق الموبايل (Flutter)

### ✅ منجز (2026-07-15) — إعداد البيئة + مشروع فارغ

- [x] Flutter SDK 3.44.6 (stable) — محمول (zip)، مثبَّت في `C:\src\flutter` على جهاز التطوير المستخدم في هذه الجلسة.
- [x] JDK 17.0.19 (Microsoft Build of OpenJDK) — محمول (zip)، `C:\src\jdk17`.
- [x] Android SDK (`cmdline-tools`, `platform-tools`, `platforms;android-34`, `platforms;android-36`, `build-tools;28.0.3`, `build-tools;34.0.0`) — `C:\Android\sdk`، كل التراخيص مقبولة.
- [x] إضافات VS Code: `Dart-Code.dart-code` و`Dart-Code.flutter`.
- [x] `flutter doctor` نظيف بالكامل ما عدا Visual Studio (غير مطلوب — لسنا نبني تطبيق Windows Desktop، فقط Android + Web).
- [x] تم إنشاء المشروع فعليًا: `flutter create --org com.trilogy.nexa --project-name employee_app mobile_app` في جذر المستودع.
- [x] **تحقق فعلي من السلسلة كاملة**: `flutter build web` نجح فعليًا على المشروع الافتراضي الفارغ (وليس مجرد نجاح تثبيت) — ناتج البناء في `mobile_app/build/web` (مُتجاهَل في `.gitignore` الفرعي الخاص بـ `mobile_app/`، لن يُرفع على git).

**⚠️ ملاحظة مهمة لأي جلسة قادمة على Windows عبر Git Bash**: استدعاء أدوات `.bat` من Android SDK (`sdkmanager.bat` خصوصًا) عبر Git Bash بمسارات Windows تحتوي `C:\...` قد يُصاب بمشكلة "path mangling" شهيرة في MSYS (لاحظنا ظهور ملف/مجلد وهمي باسم `Androidsdk` وفشل تثبيت الحزم بصمت رغم ظهور شريط تقدم "ناجح"). الحل: صدّر `MSYS_NO_PATHCONV=1` قبل استدعاء أي `.bat`/`.exe` من Android SDK، واستخدم `/` بدل `\` في مسارات الـ `--sdk_root` (مثال: `--sdk_root=C:/Android/sdk`). تحقق دائمًا من نتيجة أي أمر `sdkmanager` بعرض محتويات المجلد الفعلي بعده، لا تثق في نجاح شريط التقدم وحده.

### ⏸️ غير منجز — بناء التطبيق نفسه

المشروع في `mobile_app/` لا يزال هو الـ scaffold الافتراضي من `flutter create` (تطبيق العدّاد التجريبي) — لا توجد شاشة واحدة فعلية للموظف بعد. **نقطة الاستئناف بالضبط:**

1. إذا كنت على جهاز جديد بدون Flutter مثبَّت: كرر خطوات "إعداد البيئة" أعلاه (كل المسارات محلية لكل جهاز، لا شيء منها في الـ repo).
2. الـ API جاهز بالكامل ولا يحتاج أي تعديل: base URL = `/api/employee`, تسجيل الدخول `POST /login` بـ `{login_username, login_password, com_code, device_name}` يرجّع `token` (Sanctum) يُستخدم كـ `Authorization: Bearer` في كل الطلبات التالية. راجع `app/Http/Controllers/Api/Employee/*` و`routes/api.php` (قسم "Employee Self-Service") للـ endpoints الكاملة.
3. أضف للـ `mobile_app/pubspec.yaml`: `dio` (API client)، `flutter_secure_storage` (تخزين التوكن)، `local_auth` (بصمة/وجه الجهاز — حسب القرار D2)، `geolocator` (GPS)، `printing`/`url_launcher` (عرض/تحميل PDF)، `provider` (state management).
4. الشاشات المطلوبة: تسجيل دخول → لوحة رئيسية → قسائم الراتب → شهادة راتب → مستنداتي → طلبات الإجازة (قائمة/جديد/إلغاء) → استقالة → حضور/انصراف (بوابة بصمة الجهاز + GPS، تُخفى هذه الشاشة فقط في نسخة الويب من نفس كود Flutter لأن `local_auth` لا يدعم متصفح الويب).
5. بعد بناء الشاشات: شغّل `flutter run -d chrome` (نسخة الويب بدون الحضور) و`flutter run` على محاكي/جهاز Android حقيقي (المسار الكامل شامل الحضور — لا يوجد محاكي (AVD) مُجهَّز بعد في هذه الجلسة، ستحتاج `avdmanager create avd` أو جهاز Android حقيقي موصول عبر USB/`adb`)، وتحقق فعليًا من كل شاشة بنفس أسلوب الاختبار end-to-end المستخدم مع الـ API والبوابة (لا تكتفِ بنجاح البناء).

## ⏸️ غير منجز — Phase 3: تحسينات مؤجَّلة عمدًا (لم تُطلب بعد)

- [ ] إشعارات Push (FCM) عند تغيّر حالة الطلبات.
- [ ] خريطة مواقع الحضور للأدمن + تتبع مستمر في الخلفية (البنية التحتية جاهزة جزئيًا: `attendances.check_in_lat/lng` و`check_out_lat/lng` موجودة بالفعل، فقط تحتاج شاشة عرض في الأدمن إن طُلبت لاحقًا).
- [ ] مطابقة وجه عبر خدمة سحابية (تم رفضها عمدًا لصالح بصمة الجهاز المحلية — القرار D2؛ لو تغيّر الرأي مستقبلًا فهذا يحتاج قرار جديد بخصوص أي خدمة (AWS Rekognition/Azure Face) وتكلفتها).

## ملفات مرجعية سريعة

- API: `routes/api.php` (قسم Employee Self-Service) → `app/Http/Controllers/Api/Employee/*`
- بوابة الويب: `routes/admin.php` (بحث عن `بوابة الموظفين`) → `app/Http/Controllers/Employee/EmployeePortalController.php` → `resources/views/employee/*`
- الموديل: `app/Models/Employee.php` (الآن `extends Authenticatable`, `HasApiTokens`, hash تلقائي لكلمة المرور)
- منطق حساب الحضور المشترك: `app/Services/AttendanceCalculationService.php`
- Migrations الجديدة: ابحث عن التاريخ `2026_07_14` في `database/migrations/`
