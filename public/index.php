<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Locate the application
|--------------------------------------------------------------------------
|
| Standard layout — the web root is the app's own public/ directory:
|     /home/user/portfolio_app/public/index.php   (this file)
|
| Split layout for shared hosting — the web root is public_html/, and the
| application lives in a sibling directory outside the web root:
|     /home/user/public_html/index.php            (this file)
|     /home/user/portfolio_app/                   (everything else)
|
| Both work without editing this file. See docs/DEPLOYMENT.md.
|
*/

$appPath = __DIR__.'/..';
$splitLayout = false;

if (! file_exists($appPath.'/bootstrap/app.php') && file_exists(__DIR__.'/../portfolio_app/bootstrap/app.php')) {
    $appPath = __DIR__.'/../portfolio_app';
    $splitLayout = true;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

if ($splitLayout) {
    // Uploads, the résumé PDF, and the Vite manifest live in this directory.
    $app->usePublicPath(__DIR__);
}

$app->handleRequest(Request::capture());
