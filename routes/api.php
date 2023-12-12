<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingInfoController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CouponEventController;
use App\Http\Controllers\GeneralConfigurationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LdsLicenseController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SoftwarePackageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TaxIdController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
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

/**
 * not web server
 */
$role = env('CONTAINER_ROLE', null);
if ($role && $role != 'admin' && $role != 'customer' &&  $role != 'main') {
  return;
}

$testCode = env('APP_TEST_CODE', false);
$domainCustomer = env('DOMAIN_CUSTOMER', '');
$domainAdmin = $domainCustomer ? 'admin.' . $domainCustomer : '';


/**
 * customer routes
 */
Route::domain($domainCustomer)->group(function () use ($testCode) {
  // user authentication
  Route::middleware('auth:api')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
  });

  // account
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/me', [AuthController::class, 'me']);
    Route::get('/account/full', [UserController::class, 'fullByAccount']);
    Route::get('/account/machines', [MachineController::class, 'listByAccount']);
  });

  // account subscription
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/subscriptions', [SubscriptionController::class, 'accountList']);
    Route::post('/account/subscriptions', [SubscriptionController::class, 'create']);
    Route::get('/account/subscriptions/{id}', [SubscriptionController::class, 'accountIndex']);
    Route::delete('/account/subscriptions/{id}', [SubscriptionController::class, 'destroy']);
    Route::post('/account/subscriptions/{id}/pay', [SubscriptionController::class, 'pay']);
    Route::post('/account/subscriptions/{id}/cancel', [SubscriptionController::class, 'accountCancel']);
    Route::post('/account/subscriptions/{id}/renewal', [SubscriptionController::class, 'confirmRenewal']);

    Route::get('/account/subscriptions/{id}/refundable', [SubscriptionController::class, 'refundable']);

    Route::post('/account/tax-rate', [SubscriptionController::class, 'taxRate']);
  });

  // account billing info
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/billing-info', [BillingInfoController::class, 'accountGet']);
    Route::post('/account/billing-info', [BillingInfoController::class, 'accountSet']);
  });

  // account payment method
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/payment-method', [PaymentMethodController::class, 'accountGet']);
    Route::post('/account/payment-method', [PaymentMethodController::class, 'accountSet']);
  });

  // tax id
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/tax-ids', [TaxIdController::class, 'accountList']);
    Route::post('/account/tax-ids', [TaxIdController::class, 'accountCreate']);
    Route::get('/account/tax-ids/{id}', [TaxIdController::class, 'accountIndex']);
    Route::delete('/account/tax-ids/{id}', [TaxIdController::class, 'accountDelete']);
  });

  // account invoice
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/invoices', [InvoiceController::class, 'accountList']);
    Route::get('/account/invoices/{id}', [InvoiceController::class, 'accountIndex']);

    Route::post('/account/invoices/{id}/cancel', [InvoiceController::class, 'accountCancel']);
  });

  // account refund
  Route::middleware('auth:api')->group(function () {
    Route::get('/account/refunds', [RefundController::class, 'accountList']);
    Route::get('/account/refunds/{id}', [RefundController::class, 'accountIndex']);
  });

  // LDS
  Route::middleware('auth:api')->group(function () {
    Route::get('/lds/lds-license', [LdsLicenseController::class, 'accountGet']);
    Route::post('/lds/reg-device', [LdsLicenseController::class, 'regDevice']);
    Route::post('/lds/unreg-device', [LdsLicenseController::class, 'unregDevice']);
  });
});


/** 
 * admin routes
 */
Route::domain($domainAdmin)->group(function () {

  // admin authentication
  Route::post('/auth/admin/login',            [AdminAuthController::class, 'login']);
  Route::post('/auth/admin/forgot-password',  [AdminAuthController::class, 'forgotPassword'])->name('password.email');
  Route::post('/auth/admin/reset-password',   [AdminAuthController::class, 'resetPassword']);

  Route::middleware('auth:admin')->group(function () {
    Route::post('/auth/admin/refresh',        [AdminAuthController::class, 'refresh']);
    Route::post('/auth/admin/me',             [AdminAuthController::class, 'me']);
    Route::post('/auth/admin/logout',         [AdminAuthController::class, 'logout']);

    Route::post('/auth/admin/update-password', [AdminAuthController::class, 'updatePassword']);
  });

  // software packages
  Route::middleware('auth:admin')->group(function () {
    Route::post('/software-packages', [SoftwarePackageController::class, 'create'])->middleware('access:software-package.create');
    Route::patch('/software-packages/{id}', [SoftwarePackageController::class, 'update'])->middleware('access:software-package.update');
    Route::delete('/software-packages/{id}', [SoftwarePackageController::class, 'destroy'])->middleware('access:software-package.delete');
  });

  // configure
  Route::middleware('auth:admin')->group(function () {
    Route::get('/config/general', [GeneralConfigurationController::class, 'get']);
    Route::patch('/config/general', [GeneralConfigurationController::class, 'set'])->middleware('access:config.update');
  });

  // country
  Route::middleware('auth:admin')->group(function () {
    Route::post('/countries', [CountryController::class, 'create'])->middleware('access:country.create');
    Route::patch('/countries/{code}', [CountryController::class, 'updateWithCode'])->middleware('access:country.update');
    Route::delete('/countries/{code}', [CountryController::class, 'destroyWithCode'])->middleware('access:country.delete');
  });

  // machine
  Route::middleware('auth:admin')->group(function () {
    Route::get('/machines', [MachineController::class, 'list']);
    Route::get('/machines/{id}', [MachineController::class, 'index']);
    Route::post('/machines', [MachineController::class, 'create'])->middleware('access:machine.create');
    Route::patch('/machines/{id}', [MachineController::class, 'update'])->middleware('access:machine.update');
    Route::delete('/machines/{id}', [MachineController::class, 'destroy'])->middleware('access:machine.delete');
    Route::post('/machines/{id}/transfer', [MachineController::class, 'transfer'])->middleware('access:machine.transfer');
  });

  // coupon
  Route::middleware('auth:admin')->group(function () {
    Route::get('/coupons', [CouponController::class, 'list'])->middleware('access:coupon.list');
    Route::post('/coupons', [CouponController::class, 'create'])->middleware('access:coupon.create');
    Route::get('/coupons/{id}', [CouponController::class, 'index'])->middleware('access:coupon.get');
    Route::patch('/coupons/{id}', [CouponController::class, 'update'])->middleware('access:coupon.update');
    Route::delete('/coupons/{id}', [CouponController::class, 'destroy'])->middleware('access:coupon.delete');

    Route::get('/coupon-events', [CouponEventController::class, 'list'])->middleware('access:coupon.list');
  });

  // design plan
  Route::middleware('auth:admin')->group(function () {
    Route::get('/design-plans', [PlanController::class, 'list'])->middleware('access:design-plan.list');
    Route::post('/design-plans', [PlanController::class, 'create'])->middleware('access:design-plan.create');
    Route::get('/design-plans/{id}', [PlanController::class, 'index'])->middleware('access:design-plan.get');
    Route::patch('/design-plans/{id}', [PlanController::class, 'update'])->middleware('access:design-plan.update');
    Route::delete('/design-plans/{id}', [PlanController::class, 'destroy'])->middleware('access:design-plan.delete');
    Route::post('/design-plans/{id}/activate', [PlanController::class, 'activate'])->middleware('access:design-plan.update');
    Route::post('/design-plans/{id}/deactivate', [PlanController::class, 'deactivate'])->middleware('access:design-plan.update');

    // TODO:
    Route::get('/design-plans/{id}/history-records', [PlanController::class, 'history'])->middleware('access:design-plan.list');
  });

  // invoice
  Route::middleware('auth:admin')->group(function () {
    Route::get('/invoices', [InvoiceController::class, 'list'])->middleware('access:invoice.list');
    Route::get('/invoices/{id}', [InvoiceController::class, 'index'])->middleware('access:invoice.get');

    Route::post('/invoices/{id}/cancel', [InvoiceController::class, 'cancel'])->middleware('access:invoice.cancel');
  });


  // refund
  Route::middleware('auth:admin')->group(function () {
    Route::get('/refunds', [RefundController::class, 'list'])->middleware('access:refund.list');
    Route::get('/refunds/{id}', [RefundController::class, 'index'])->middleware('access:refund.get');

    Route::post('/refunds', [RefundController::class, 'create'])->middleware('access:refund.create');
  });

  // subscription
  Route::middleware('auth:admin')->group(function () {
    Route::get('/subscriptions', [SubscriptionController::class, 'list'])->middleware('access:subscription.list');
    Route::get('/subscriptions/{id}', [SubscriptionController::class, 'index'])->middleware('access:subscription.get');
    Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel'])->middleware('access:subscription.cancel');
    Route::post('/subscriptions/{id}/stop', [SubscriptionController::class, 'stop'])->middleware('access:subscription.stop');
  });

  // user
  Route::middleware('auth:admin')->group(function () {
    Route::get('/users', [UserController::class, 'list']);
    Route::get('/users/{id}', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'create'])->middleware('access:user.create');
    Route::post('/users/{id}', [UserController::class, 'refresh'])->middleware('access:user.refresh');
    Route::post('/users/{id}/details', [UserController::class, 'updateDetails'])->middleware('access:user.update');
  });

  // user additional info (billing_info, payment_method, license)
  Route::middleware('auth:admin')->group(function () {
    Route::get('/users/{id}/billing-info', [BillingInfoController::class, 'userGet'])->middleware('access:user.billing-info.get');
    Route::get('/users/{id}/payment-method', [PaymentMethodController::class, 'userGet'])->middleware('access:user.payment-method.get');
    Route::get('/users/{id}/lds-license', [LdsLicenseController::class, 'userGet'])->middleware('access:user.lds-license.get');
    Route::get('/users/{id}/machines', [MachineController::class, 'listByUser'])->middleware('access:user.machine.list');
    Route::get('/users/{id}/subscriptions', [SubscriptionController::class, 'listByUser'])->middleware('access:user.subscription.list');
  });

  // admin users
  Route::middleware('auth:admin')->group(function () {
    Route::get('/admin-users', [AdminUserController::class, 'list'])->middleware('access:admin-user.list');
    Route::get('/admin-users/{id}', [AdminUserController::class, 'index'])->middleware('access:admin-user.get');
    Route::post('/admin-users', [AdminUserController::class, 'create'])->middleware('access:admin-user.create');
    Route::patch('/admin-users/{id}', [AdminUserController::class, 'update'])->middleware('access:admin-user.update');
    Route::delete('/admin-users/{id}', [AdminUserController::class, 'destroy'])->middleware('access:admin-user.delete');
  });

  // report
  Route::middleware('auth:admin')->group(function () {
    Route::get('/x-ray/summary', [ReportController::class, 'summary'])->middleware('access:x-ray.summary');
    Route::get('/x-ray/statistic-records', [ReportController::class, 'listStaticsRecord'])->middleware('access:x-ray.summary');
  });

  // TODO: ip whitelist
  // webhook
  Route::post('/dr/webhooks', [WebhookController::class, 'handler']);
});


/**
 * public routes
 */

// public country
Route::get('/countries', [CountryController::class, 'list']);
Route::get('/countries/{code}', [CountryController::class, 'indexWithCode']);

// public plans
Route::get('/plans', [PlanController::class, 'listPlan']);
Route::get('/plans/{id}', [PlanController::class, 'indexPlan']);

// public coupon
Route::post('/coupon-validate', [CouponController::class, 'check']);

// software packages
Route::get('/software-packages', [SoftwarePackageController::class, 'list']);
Route::get('/software-packages/{id}', [SoftwarePackageController::class, 'index']);


// fake login route (just to make sure that route('login') shall not fail)
Route::get('/fake-login', function () {
  return response()->json(['message' => 'Not found'], 404);
})->name('login');


//
// fall back
//
Route::fallback(function () {
  return response()->json(['message' => 'Not found'], 404);
});
