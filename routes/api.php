<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralConfigurationController;
use App\Http\Controllers\LdsRegistrationController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SoftwarePackageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
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

$role = env('CONTAINER_ROLE', null);
$testCode = env('APP_TEST_CODE', false);


// 
// admin authentication
// 
if (!$role || $role == 'admin') {
  Route::post('/auth/admin/login',            [AdminAuthController::class, 'login']);
  Route::post('/auth/admin/forgot-password',  [AdminAuthController::class, 'forgotPassword'])->name('password.email');
  Route::post('/auth/admin/reset-password',   [AdminAuthController::class, 'resetPassword']);

  Route::middleware('auth:admin')->group(function () {
    Route::post('/auth/admin/refresh',        [AdminAuthController::class, 'refresh']);
    Route::post('/auth/admin/me',             [AdminAuthController::class, 'me']);
    Route::post('/auth/admin/logout',         [AdminAuthController::class, 'logout']);

    Route::post('/auth/admin/update-password', [AdminAuthController::class, 'updatePassword']);
  });
}


//
// user authentication
//
if (!$role || $role == 'customer') {
  Route::middleware('auth:api')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
  });
}

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

if (!$role || $role == 'admin') {
  Route::middleware('auth:admin')->group(function () {
    Route::post('/software-packages', [SoftwarePackageController::class, 'create'])->middleware('access:software-package.create');
    Route::patch('/software-packages/{id}', [SoftwarePackageController::class, 'update'])->middleware('access:software-package.update');
    Route::delete('/software-packages/{id}', [SoftwarePackageController::class, 'destroy'])->middleware('access:software-package.delete');
  });
}

//
// configure
//
if (!$role || $role == 'admin') {
  Route::middleware('auth:admin')->group(function () {
    Route::get('/config/general', [GeneralConfigurationController::class, 'get']);
    Route::patch('/config/general', [GeneralConfigurationController::class, 'set'])->middleware('access:config.update');
  });
}

//
// LDS
//
if (!$role || $role == 'customer') {
  Route::middleware('auth:api')->group(function () {
    Route::post('/lds/reg-device', [LdsRegistrationController::class, 'regDevice']);
    Route::post('/lds/unreg-device', [LdsRegistrationController::class, 'unregDevice']);
  });
}

//
// machine
//
if (!$role || $role == 'admin') {
  Route::middleware('auth:admin')->group(function () {
    Route::get('/machines', [MachineController::class, 'list']);
    Route::get('/machines/{id}', [MachineController::class, 'index']);
    Route::post('/machines', [MachineController::class, 'create'])->middleware('access:machine.create');
    Route::patch('/machines/{id}', [MachineController::class, 'update'])->middleware('access:machine.update');
    Route::delete('/machines/{id}', [MachineController::class, 'destroy'])->middleware('access:machine.delete');
    Route::post('/machines/{id}/transfer', [MachineController::class, 'transfer'])->middleware('access:machine.transfer');
  });
}


// 
// user
// 
if (!$role || $role == 'admin') {
  Route::middleware('auth:admin')->group(function () {
    Route::get('/users', [UserController::class, 'list']);
    Route::get('/users/{id}', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'create'])->middleware('access:user.create');
    Route::post('/users/{id}', [UserController::class, 'refresh'])->middleware('access:user.refresh');

    Route::get('/users/{id}/full', [UserController::class, 'full']);
    Route::get('/users/{id}/machines', [MachineController::class, 'listByUser']);
    Route::get('/users/{id}/subscriptions', [SubscriptionController::class, 'listByUser']);
  });
}

// 
// admin users
// 
if (!$role || $role == 'admin') {
  Route::middleware('auth:admin')->group(function () {
    Route::get('/admin-users', [AdminUserController::class, 'list'])->middleware('access:admin-user.list');
    Route::get('/admin-users/{id}', [AdminUserController::class, 'index'])->middleware('access:admin-user.get');
    Route::post('/admin-users', [AdminUserController::class, 'create'])->middleware('access:admin-user.create');
    Route::patch('/admin-users/{id}', [AdminUserController::class, 'update'])->middleware('access:admin-user.update');
    Route::delete('/admin-users/{id}', [AdminUserController::class, 'destroy'])->middleware('access:admin-user.delete');
  });
}

// 
// report
// 
if (!$role || $role == 'admin') {
  Route::middleware('auth:admin')->group(function () {
    Route::get('/x-ray/summary', [ReportController::class, 'summary'])->middleware('access:x-ray.summary');
    Route::post('/report/subscriptions', [ReportController::class, 'subscriptions']);
  });
}


//
// account
//
if (!$role || $role == 'customer') {
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/me', [AuthController::class, 'me']);
    Route::get('/account/full', [UserController::class, 'fullByAccount']);
    Route::get('/account/machines', [MachineController::class, 'listByAccount']);
    Route::get('/account/subscriptions', [SubscriptionController::class, 'listByAccount']);
  });
}


//
// test: not in production version
//
if ($testCode) {
  Route::get('test/reset-data', [TestController::class, 'resetData']);
  Route::post('test/reset-data', [TestController::class, 'resetData']);
}

//
// fake login route (just to make sure that route('login') shall not fail)
//
Route::get('/fake-login', function () {
  return response()->json(['message' => 'Not found'], 404);
})->name('login');

//
// fall back
//
Route::get('/{any}', function () {
  return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');
