<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LdsController;
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
if (!$role || $role == 'lds') {
  Route::get('/check-in', [LdsController::class, 'checkIn']);
  Route::get('/check-out', [LdsController::class, 'checkOut']);
}


/**
 * fallback 
 */
Route::fallback(function () {
  return view('index');
});
