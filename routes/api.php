<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

include __DIR__ . "/apiX.php";

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::prefix('v1')->group(function () {
  // admin authentication
  Route::post('/auth/admin/login', [AdminAuthController::class, 'login']);
  Route::post('/auth/admin/forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('password.email');
  Route::post('/auth/admin/reset-password', [AdminAuthController::class, 'resetPassword']);

  Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/admin/logout', [AdminAuthController::class, 'logout']);
    Route::post('/auth/admin/update-password', [AdminAuthController::class, 'updatePassword']);
  });

  // public routes
  Route::get('/auth/me', [AuthController::class, 'me']);

  // authenticated routes
  Route::middleware('auth:sanctum')->group(function () {
    Route::post('/token/create', [TokenController::class, 'create']);
  });
});

Route::get('/{any}', function () {
  return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');
