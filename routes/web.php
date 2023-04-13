<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LdsController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$role = env('CONTAINER_ROLE', null);
$testCode = env('APP_TEST_CODE', false);

/**
 * authentication
 */

if (!$role || $role == 'customer') {
  Route::get('/auth/login', [AuthController::class, 'loginWeb']);
  Route::get('/auth/logout', [AuthController::class, 'logoutWeb']);
}

if (!$role || $role == 'admin') {
  Route::get('/admin/auth/reset-password', function () {
    return view('index');
  })->name('password.reset');
}

/**
 * LDS
 */
if (!$role || $role == 'customer') {
  Route::get('/check-in', [LdsController::class, 'checkIn']);
  Route::get('/check-out', [LdsController::class, 'checkOut']);
}

/**
 * Backend Test
 */
if ($testCode) {

  Route::get('/be-test/dr', function () {
    return view('dr-test');
  });

  Route::get('/be-test/mail/{type}', [TestController::class, 'sendMail']);
  Route::get('/be-test/notification/{type}', [TestController::class, 'viewNotification']);
}

//
// TODO: remove: test mail sending
// 



/**
 * fallback 
 */
Route::fallback(function () {
  return view('index');
});
