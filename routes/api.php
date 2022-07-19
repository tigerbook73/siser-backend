<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralConfigurationController;
use App\Http\Controllers\JwtAuthController;
use App\Http\Controllers\LdsController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SoftwarePackageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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


// 
// admin authentication
// 
Route::post('/auth/admin/login',            [AdminAuthController::class, 'login']);
Route::post('/auth/admin/forgot-password',  [AdminAuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/auth/admin/reset-password',   [AdminAuthController::class, 'resetPassword']);

Route::middleware('auth:admin')->group(function () {
  Route::post('/auth/admin/refresh',        [AdminAuthController::class, 'refresh']);
  Route::post('/auth/admin/me',             [AdminAuthController::class, 'me']);
  Route::post('/auth/admin/logout',         [AdminAuthController::class, 'logout']);

  Route::post('/auth/admin/update-password', [AdminAuthController::class, 'updatePassword']);
});


//
// user authentication
//
Route::post('/auth/login-test', [AuthController::class, 'loginTest']);  // TODO: login is in web route, this is for test

Route::middleware('auth:api')->group(function () {
  Route::post('/auth/refresh', [AuthController::class, 'refresh']);
  Route::post('/auth/me', [AuthController::class, 'me']);
  Route::post('/auth/logout', [AuthController::class, 'logout']);
});


// 
// plans
// 
Route::get('/plans', [PlanController::class, 'list']);
Route::get('/plans/{id}', [PlanController::class, 'index']);


// 
// software packages
// 
Route::get('/software-packages', [SoftwarePackageController::class, 'list']);
Route::get('/software-packages/{id}', [SoftwarePackageController::class, 'index']);

Route::middleware('auth:admin')->group(function () {
  Route::post('/software-packages', [SoftwarePackageController::class, 'create']);
  Route::patch('/software-packages/{id}', [SoftwarePackageController::class, 'update']);
});


//
// configure
//
Route::middleware('auth:admin')->group(function () {
  Route::get('/config/general', [GeneralConfigurationController::class, 'get']);
  Route::patch('/config/general', [GeneralConfigurationController::class, 'set']);
});


//
// LDS
//
Route::middleware('auth:api')->group(function () {
  Route::post('/lds/reg-device', [LdsController::class, 'regDevice']);
});

//
// machine
//
Route::middleware('auth:admin')->group(function () {
  Route::get('/machines', [MachineController::class, 'list']);
  Route::get('/machines/{id}', [MachineController::class, 'index']);
  Route::post('/machines', [MachineController::class, 'create']);
  Route::patch('/machines/{id}', [MachineController::class, 'update']);
});


// 
// user
// 
Route::middleware('auth:admin')->group(function () {
  Route::get('/users', [UserController::class, 'list']);
  Route::get('/users/{id}', [UserController::class, 'index']);
  Route::post('/users', [UserController::class, 'create']);
  Route::post('/users/{id}', [UserController::class, 'refresh']);

  Route::get('/users/{id}/full', [UserController::class, 'fullByUser']);
  Route::get('/users/{id}/machines', [MachineController::class, 'listByUser']);
  Route::get('/users/{id}/subscriptions', [SubscriptionController::class, 'listByUser']);
});

// 
// admin users
// 
Route::middleware('auth:admin')->group(function () {
  Route::get('/admin-users', [AdminUserController::class, 'list']);
  Route::get('/admin-users/{id}', [AdminUserController::class, 'index']);
  Route::post('/admin-users', [AdminUserController::class, 'create']);
  Route::patch('/admin-users/{id}', [AdminUserController::class, 'update']);
});

// 
// report
// 
Route::middleware('auth:admin')->group(function () {
  Route::post('/report/subscriptions', [ReportController::class, 'subscriptions']);
});


//
// account
//
Route::middleware('auth:api')->group(function () {
  Route::get('/account/me', [AuthController::class, 'me']);
  Route::get('/account/full', [UserController::class, 'fullByLoginUser']);
  Route::get('/account/machines', [MachineController::class, 'listByLoginUser']);
  Route::get('/account/subscriptions', [SubscriptionController::class, 'listByLoginUser']);
});


//
// test
//
Route::post('test/reset-data', [TestController::class, 'resetData']);

//
// fall back
//
Route::get('/{any}', function () {
  return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');
