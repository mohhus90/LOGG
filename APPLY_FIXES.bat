@echo off
REM ============================================================
REM LOGG HR — تطبيق الإصلاحات النهائية
REM شغّل هذا الملف من مجلد المشروع LOGG
REM ============================================================

echo === نسخ الملفات المُصلَحة ===

REM 1. الإصلاح الجذري — AdminPanelSettingController
copy /Y "LOGG-fixes-v2\AdminPanelSettingController_FINAL.php" "app\Http\Controllers\AdminPanelSettingController.php"
echo [OK] AdminPanelSettingController.php

REM 2. KpiController
copy /Y "LOGG-fixes-v2\KpiController.php" "app\Http\Controllers\Admin\KpiController.php"
echo [OK] KpiController.php

REM 3. Finance_calender model
copy /Y "LOGG-fixes-v2\Finance_calender.php" "app\Models\Finance_calender.php"
echo [OK] Finance_calender.php

REM 4. Finance_calendersController
copy /Y "LOGG-fixes-v2\Finance_calendersController.php" "app\Http\Controllers\Admin\Finance_calendersController.php"
echo [OK] Finance_calendersController.php

REM 5. Register view
copy /Y "LOGG-fixes-v2\register.blade.php" "resources\views\admin\auth\register.blade.php"
echo [OK] register.blade.php

REM 6. Migration لـ sort_order
copy /Y "LOGG-fixes-v2\2026_05_17_100001_add_sort_order_to_kpi_definitions.php" "database\migrations\"
echo [OK] KPI sort_order migration

REM === تشغيل الـ migrations ===
echo === تشغيل المزامنة ===
php artisan migrate --force

REM === مسح الـ Cache ===
echo === مسح الكاش ===
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo === تم بنجاح ===
pause
