<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Fix REQUEST_URI for subdirectory installations (e.g. XAMPP: localhost/NEXA/)
// Symfony determines route path by stripping SCRIPT_NAME directory from REQUEST_URI.
// When Apache rewrites internally, REQUEST_URI keeps the full /NEXA/... prefix
// but SCRIPT_NAME points to /NEXA/public/index.php — Symfony can't align them.
// We adjust SCRIPT_NAME to /NEXA/index.php so Symfony strips /NEXA correctly.
if (PHP_SAPI !== 'cli' && isset($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT'])) {
    $scriptDir  = str_replace('\\', '/', dirname($_SERVER['SCRIPT_FILENAME']));
    $docRoot    = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $prefix     = substr($scriptDir, strlen($docRoot));   // e.g. /NEXA/public
    $parentDir  = dirname($prefix);                       // e.g. /NEXA

    if ($parentDir !== '/' && $parentDir !== '.'
        && isset($_SERVER['REQUEST_URI'])
        && str_starts_with($_SERVER['REQUEST_URI'], $parentDir . '/')
    ) {
        $_SERVER['SCRIPT_NAME'] = $parentDir . '/index.php';
        $_SERVER['PHP_SELF']    = $parentDir . '/index.php';
    }
}

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
