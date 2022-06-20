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

Route::get('/auth/login', [AuthController::class, 'login'])->name('login');
Route::get('/auth/logout', [AuthController::class, 'logout']);

/**
 * 
 */

/**
 * fallback 
 */
Route::fallback(function () {
  return view('index');
});
