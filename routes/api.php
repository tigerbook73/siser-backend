<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralConfigurationController;
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
Route::post('/auth/admin/login', [AdminAuthController::class, 'login']);
Route::post('/auth/admin/forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/auth/admin/reset-password', [AdminAuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/auth/admin/logout', [AdminAuthController::class, 'logout']);
  Route::post('/auth/admin/password', [AdminAuthController::class, 'updatePassword']);
});
Route::post('/auth/admin/token', [AdminAuthController::class, 'token']);


// 
// public
// 
Route::get('/plans', [PlanController::class, 'list']);
Route::get('/plans/{id}', [PlanController::class, 'index']);
Route::get('/software-packages', [SoftwarePackageController::class, 'list']);
Route::get('/software-packages/{id}', [SoftwarePackageController::class, 'index']);


//
// authentication route
// 

// TODO: temp disable auth
/*
Route::middleware('auth:sanctum')->group(function () {
  if (config('app.role') == 'admin') {
*/

// configure
Route::get('/config/general', [GeneralConfigurationController::class, 'get']);
Route::patch('/config/general', [GeneralConfigurationController::class, 'set']);

// machine
Route::get('/machines', [MachineController::class, 'list']);
Route::get('/machines/{id}', [MachineController::class, 'index']);
// 
Route::post('/machines', [MachineController::class, 'create']);
Route::delete('/machines/{id}', [MachineController::class, 'destroy']);

// plan
Route::patch('/plans/{id}', [PlanController::class, 'update']);

// software package
Route::post('/software-packages', [SoftwarePackageController::class, 'create']);
Route::patch('/software-packages/{id}', [SoftwarePackageController::class, 'update']);

// user
Route::get('/users', [UserController::class, 'list']);
Route::get('/users/{id}', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'create']);
Route::post('/users/{id}', [UserController::class, 'refresh']);

Route::get('/users/{id}/full', [UserController::class, 'fullByUser']);
Route::get('/users/{id}/machines', [MachineController::class, 'listByUser']);
Route::get('/users/{id}/subscriptions', [SubscriptionController::class, 'listByUser']);

// admin users
Route::get('/admin-users', [AdminUserController::class, 'list']);
Route::get('/admin-users/{id}', [AdminUserController::class, 'index']);
Route::post('/admin-users', [AdminUserController::class, 'create']);
Route::patch('/admin-users/{id}', [AdminUserController::class, 'update']);

// report
Route::post('/report/subscriptions', [ReportController::class, 'subscriptions']);

// TODO: temp disable auth
/*
  }

  if (config('app.role') == 'customer') {
*/

Route::get('/account/me', [AuthController::class, 'me']);

Route::get('/account/full', [UserController::class, 'fullByLoginUser']);
Route::get('/account/machines', [MachineController::class, 'listByLoginUser']);
Route::get('/account/subscriptions', [SubscriptionController::class, 'listByLoginUser']);

// TODO: temp disable auth
/*
  }
});
*/


Route::post('test/reset-data', [TestController::class, 'resetData']);

Route::get('/{any}', function () {
  return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');
