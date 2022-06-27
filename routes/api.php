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


// 
// admin authentication
// 
Route::post('/auth/admin/login', [AdminAuthController::class, 'login']);
Route::post('/auth/admin/forgot-password', [AdminAuthController::class, 'forgotPassword'])->name('password.email');
Route::post('/auth/admin/reset-password', [AdminAuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('/auth/admin/logout', [AdminAuthController::class, 'logout']);
  Route::post('/auth/admin/update-password', [AdminAuthController::class, 'updatePassword']);
});


//
// customer ??
//
Route::get('/auth/me', [AuthController::class, 'me']);
Route::middleware('auth:sanctum')->group(function () {
  Route::post('/token/create', [TokenController::class, 'create']);
});


// 
// public
// 
Route::get('/plans', [App\Mockup\Controllers\PublicController::class, 'listPlan']);
Route::get('/plans/{id}', [App\Mockup\Controllers\PublicController::class, 'getPlan']);
Route::get('/software-packages', [App\Mockup\Controllers\PublicController::class, 'listSoftwarePackages']);
Route::get('/software-packages/{id}', [App\Mockup\Controllers\PublicController::class, 'getSoftwarePackage']);


//
// authentication route
// 

// TODO: temp disable auth
/*
Route::middleware('auth:sanctum')->group(function () {
  if (config('app.role') == 'admin') {
*/

// configure
Route::get('/config/general', [App\Mockup\Controllers\AdminPortalController::class, 'getConfigGeneral']);
Route::patch('/config/general', [App\Mockup\Controllers\AdminPortalController::class, 'updateConfigGeneral']);

// machine
Route::get('/machines', [App\Mockup\Controllers\AdminPortalController::class, 'listMachine']);
Route::get('/machines/{id}', [App\Mockup\Controllers\AdminPortalController::class, 'getMachine']);
// 
Route::post('/machines', [App\Mockup\Controllers\SiserBackendController::class, 'createMachine']);
Route::delete('/machines/{id}', [App\Mockup\Controllers\SiserBackendController::class, 'deleteMachine']);
Route::post('/machines/{id}/transfer', [App\Mockup\Controllers\SiserBackendController::class, 'transferMachine']);

// plan
Route::post('/plans', [App\Mockup\Controllers\AdminPortalController::class, 'createPlan']);
Route::delete('/plans/{id}', [App\Mockup\Controllers\AdminPortalController::class, 'deletePlan']);
Route::patch('/plans/{id}', [App\Mockup\Controllers\AdminPortalController::class, 'updatePlan']);
Route::post('/plans/{id}/deactivate', [App\Mockup\Controllers\AdminPortalController::class, 'deactivatePlan']);

// software package
Route::post('/software-packages', [App\Mockup\Controllers\AdminPortalController::class, 'createSoftwarePackage']);
Route::patch('/software-packages/{id}', [App\Mockup\Controllers\AdminPortalController::class, 'updateSoftwarePackage']);

// user
Route::post('/users', [App\Mockup\Controllers\AdminPortalController::class, 'createUser']);
Route::get('/users', [App\Mockup\Controllers\AdminPortalController::class, 'listUser']);
Route::get('/users/{id}', [App\Mockup\Controllers\AdminPortalController::class, 'getUser']);
Route::post('/users/{id}', [App\Mockup\Controllers\AdminPortalController::class, 'updateUser']);
Route::post('/users/{id}/invoice-details', [App\Mockup\Controllers\AdminPortalController::class, 'createUserDetails']);
Route::get('/users/{id}/invoice-details', [App\Mockup\Controllers\AdminPortalController::class, 'getUserDetail']);
Route::patch('/users/{id}/invoice-details', [App\Mockup\Controllers\AdminPortalController::class, 'updateUserDetails']);
Route::get('/users/{id}/machines', [App\Mockup\Controllers\AdminPortalController::class, 'userGetMachines']);
Route::get('/users/{id}/subscription', [App\Mockup\Controllers\AdminPortalController::class, 'getUserSubscription']);

// report
Route::post('/report/subscriptions', [App\Mockup\Controllers\SiserBackendController::class, 'createSubscriptionReport']);

// TODO: temp disable auth
/*
  }

  if (config('app.role') == 'customer') {
*/
Route::post('/account/invoice-details', [App\Mockup\Controllers\CustomerPortalController::class, 'accountCreateDetail']);
Route::get('/account/invoice-details', [App\Mockup\Controllers\CustomerPortalController::class, 'accountGetDetail']);
Route::patch('/account/invoice-details', [App\Mockup\Controllers\CustomerPortalController::class, 'accountUpdateDetail']);
Route::get('/account/invoices', [App\Mockup\Controllers\CustomerPortalController::class, 'accountListInvoices']);
Route::get('/account/invoices/{id}', [App\Mockup\Controllers\CustomerPortalController::class, 'accountGetInvoice']);
Route::get('/account/machines', [App\Mockup\Controllers\CustomerPortalController::class, 'accountGetMachines']);
Route::post('/account/payment-method', [App\Mockup\Controllers\CustomerPortalController::class, 'accountCreatePaymentMethod']);
Route::get('/account/payment-method', [App\Mockup\Controllers\CustomerPortalController::class, 'accountGetPaymentMethod']);
Route::get('/account/subscription', [App\Mockup\Controllers\CustomerPortalController::class, 'accountGetSubscription']);

// TODO: temp disable auth
/*
  }
});
*/



Route::get('/{any}', function () {
  return response()->json(['message' => 'Not found'], 404);
})->where('any', '.*');
