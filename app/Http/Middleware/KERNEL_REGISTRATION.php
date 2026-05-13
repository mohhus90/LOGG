<?php
/**
 * ============================================================
 * أضف هذا السطر في ملف: app/Http/Kernel.php
 * داخل مصفوفة: protected $routeMiddleware = [ ... ]
 * ============================================================
 *
 * 'admin.permission' => \App\Http\Middleware\CheckAdminPermission::class,
 *
 *
 * مثال على الملف بعد التعديل:
 * ============================================================
 *
 * protected $routeMiddleware = [
 *     'auth'             => \App\Http\Middleware\Authenticate::class,
 *     'auth.basic'       => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
 *     // ... باقي الـ middleware الموجود
 *
 *     // ← أضف هذا السطر:
 *     'admin.permission' => \App\Http\Middleware\CheckAdminPermission::class,
 * ];
 */
