<?php

use App\Http\Controllers\AuthController;
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

/**
 * authentication
 */

Route::get('/auth/login', [AuthController::class, 'login']);

Route::get('/admin/auth/reset-password', function () {
  return view('index');
})->name('password.reset');

/**
 * 
 */

/**
 * fallback 
 */
Route::fallback(function () {
  return view('index');
});
