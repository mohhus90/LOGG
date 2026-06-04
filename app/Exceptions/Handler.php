<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (Throwable $e, $request) {
            // لا نتدخل في هذه الأنواع — Laravel يتعامل معها تلقائياً
            if ($e instanceof ValidationException)    return null;
            if ($e instanceof AuthenticationException) return null; // تتولاها unauthenticated()

            // تسجيل الخطأ الحقيقي
            Log::error('[Handler] ' . get_class($e) . ': ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url'  => $request->fullUrl(),
            ]);

            // ─── طلبات AJAX / API ───
            if ($request->expectsJson() || $request->is('api/*')) {
                $code    = $this->httpCode($e);
                $message = $this->safeMessage($e);
                return response()->json(['error' => $message], $code);
            }

            // ─── طلبات الويب العادية ───
            $message = $this->safeMessage($e);
            $code    = $this->httpCode($e);

            // أخطاء قابلة للعرض برسالة flash في الصفحة السابقة (403, 405, 500)
            if ($request->header('referer') && in_array($code, [403, 405, 422, 500])) {
                return redirect()->back()->with('error', $message);
            }

            // صفحة خطأ مخصصة حسب الكود (404 مثلاً)
            if (view()->exists("errors.$code")) {
                return response()->view("errors.$code", ['message' => $message], $code);
            }

            return response()->view('errors.generic', ['message' => $message, 'code' => $code], $code);
        });
    }

    // ─────────────────────────────────────────────
    // رسائل آمنة للمستخدم (بدون تفاصيل SQL/PHP)
    // ─────────────────────────────────────────────
    private function safeMessage(Throwable $e): string
    {
        if ($e instanceof QueryException) {
            return 'حدث خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني.';
        }

        if ($e instanceof NotFoundHttpException) {
            return 'الصفحة أو السجل المطلوب غير موجود.';
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return 'طريقة الطلب غير مسموح بها.';
        }

        if ($e instanceof AuthorizationException || ($e instanceof HttpException && $e->getStatusCode() === 403)) {
            return 'ليس لديك صلاحية للوصول إلى هذه الصفحة.';
        }

        if ($e instanceof AuthenticationException) {
            return 'يرجى تسجيل الدخول أولاً.';
        }

        if ($e instanceof HttpException) {
            return $e->getMessage() ?: 'حدث خطأ في الطلب. يرجى المحاولة مرة أخرى.';
        }

        // أي استثناء آخر — لا نكشف التفاصيل التقنية
        return 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى أو التواصل مع الدعم الفني.';
    }

    private function httpCode(Throwable $e): int
    {
        if ($e instanceof NotFoundHttpException)          return 404;
        if ($e instanceof AuthorizationException)         return 403;
        if ($e instanceof AuthenticationException)        return 401;
        if ($e instanceof MethodNotAllowedHttpException)  return 405;
        if ($e instanceof HttpException)                  return $e->getStatusCode();
        return 500;
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'يرجى تسجيل الدخول أولاً.'], 401);
        }

        $guard = $exception->guards()[0] ?? null;
        if ($guard === 'admin') {
            return redirect()->route('admin.dashboard.login');
        }
        return redirect()->route('login');
    }
}
