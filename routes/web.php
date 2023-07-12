<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LdsLicenseController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| IMPORTANT: When adding new routes in this file, you may need to update
| the Nginx configuration: common.conf about the proxy rules
|
*/


$role = env('CONTAINER_ROLE', null);
if ($role && $role != 'admin' && $role != 'customer' &&  $role != 'main') {
  return;
}

$testCode = env('APP_TEST_CODE', false);
$domainCustomer = env('DOMAIN_CUSTOMER', '');
$domainAdmin = $domainCustomer ? 'admin.' . $domainCustomer : '';
$domainLds = $domainCustomer ? 'lds.' . $domainCustomer : '';


/**
 * customer routes
 */
Route::domain($domainCustomer)->group(function () use ($testCode) {
  // authentication
  Route::get('/auth/login', [AuthController::class, 'loginWeb']);
  Route::get('/auth/logout', [AuthController::class, 'logoutWeb']);

  // LDS
  Route::get('/check-in', [LdsLicenseController::class, 'checkIn']);
  Route::get('/check-out', [LdsLicenseController::class, 'checkOut']);

  // Customer portal test
  if ($testCode) {
    Route::get('/auth/login-without-password', [AuthController::class, 'loginTest']);
  }
});

/**
 * lds routes
 */
Route::domain($domainLds)->group(function () {
  // LDS
  Route::get('/check-in', [LdsLicenseController::class, 'checkIn']);
  Route::get('/check-out', [LdsLicenseController::class, 'checkOut']);

  Route::fallback(function () {
    return  response('', 404);
  });
});

/** 
 * admin routes
 */
Route::domain($domainAdmin)->group(function () use ($role) {
  Route::get('/admin/auth/reset-password', function () {
    return redirect('/error/NotFound' . '?' . http_build_query(['url' => request()->url()]));
  })->name('password.reset');
});


/**
 * Backend Test
 */
if ($testCode) {
  Route::get('/be-test/mail/{type}', [TestController::class, 'sendMail']);
  Route::get('/be-test/notification/{type}', [TestController::class, 'viewNotification']);
}

//
// health check
//
Route::get('/health-check', function () {
  return response('OK');
});


Route::fallback(function () {
  return redirect('/error/NotFound' . '?' . http_build_query(['url' => request()->url()]));
});
