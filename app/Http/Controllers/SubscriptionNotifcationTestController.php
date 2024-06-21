<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Test\SubscriptionNotificationTest;
use App\Models\Country;
use App\Models\SubscriptionRenewal;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionNotifcationTestController extends Controller
{
  public function prepare(string $type, string $country, string $plan, string $coupon = null, int $licenseCount = 0): SubscriptionNotificationTest|null
  {
    $mockup = SubscriptionNotificationTest::init($country, $plan, $coupon);

    // skip invalid free-trial scenario
    if (
      in_array($type, [
        SubscriptionNotification::NOTIF_CANCELLED_REFUND,
        SubscriptionNotification::NOTIF_FAILED,
        SubscriptionNotification::NOTIF_INVOICE_PENDING,
        SubscriptionNotification::NOTIF_LAPSED,
        SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO,
        SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO,
        SubscriptionNotification::NOTIF_ORDER_INVOICE,
        SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED,
        SubscriptionNotification::NOTIF_ORDER_REFUNDED,
        SubscriptionNotification::NOTIF_EXTENDED,
        SubscriptionNotification::NOTIF_RENEW_REQUIRED,
        SubscriptionNotification::NOTIF_RENEW_REQ_CONFIRMED,
        SubscriptionNotification::NOTIF_RENEW_EXPIRED,
        SubscriptionNotification::NOTIF_SOURCE_INVALID,
      ])
      && $coupon == 'free-trial'
    ) {
      return null;
    }

    // skip invalid renewal scenario
    if (
      in_array($type, [
        SubscriptionNotification::NOTIF_RENEW_REQUIRED,
        SubscriptionNotification::NOTIF_RENEW_REQ_CONFIRMED,
        SubscriptionNotification::NOTIF_RENEW_EXPIRED,
      ])
      && ($plan !== 'year' || $country !== 'DE')
    ) {
      return null;
    }

    if ($coupon) {
      $mockup->updateCoupon($coupon);
    }

    switch ($type) {
      case SubscriptionNotification::NOTIF_ORDER_ABORTED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_FAILED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 0,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_FAILED);
        break;

      case SubscriptionNotification::NOTIF_ORDER_CANCELLED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_FAILED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 0,
          licenseCount: $licenseCount
        );
        $mockup->subscription->end_date = now();
        $mockup->subscription->save();
        $mockup->updateInvoice(status: Invoice::STATUS_CANCELLED);
        break;

      case SubscriptionNotification::NOTIF_ORDER_REFUNDED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_REFUNDED);
        $mockup->updateRefund(true);
        break;

      case SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_REFUND_FAILED);
        $mockup->updateRefund(false);
        break;

      case SubscriptionNotification::NOTIF_ORDER_CONFIRMED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->createRenewal();
        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_CANCELLED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_CANCELLING,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        if ($mockup->subscription->isFreeTrial()) {
          $mockup->subscription->stop(Subscription::STATUS_STOPPED, 'cancelled');
        } else {
          $mockup->subscription->end_date = $mockup->subscription->current_period_end_date;
        }
        $mockup->subscription->save();

        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_CANCELLED_REFUND:
        $mockup->updateSubscription(
          status: Subscription::STATUS_STOPPED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->stop(Subscription::STATUS_STOPPED, 'cancelled and refunded');
        $mockup->updateInvoice(status: Invoice::STATUS_REFUNDING);
        break;

      case SubscriptionNotification::NOTIF_REMINDER:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_INIT, next: true);
        break;

      case SubscriptionNotification::NOTIF_RENEW_REQUIRED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->createRenewal();
        $mockup->subscription->activatePendingRenewal();
        $mockup->subscription->updateActiveRenewalSubstatus(SubscriptionRenewal::SUB_STATUS_FIRST_REMINDERED);
        break;

      case SubscriptionNotification::NOTIF_RENEW_REQ_CONFIRMED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->createRenewal();
        $mockup->subscription->activatePendingRenewal();
        $mockup->subscription->completeActiveRenewal();
        break;

      case SubscriptionNotification::NOTIF_RENEW_EXPIRED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->createRenewal();
        $mockup->subscription->activatePendingRenewal();
        $mockup->subscription->expireActiveRenewal();

        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_CANCELLING,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->end_date = $mockup->subscription->current_period_end_date;
        $mockup->subscription->save();

        break;

      case SubscriptionNotification::NOTIF_INVOICE_PENDING:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_PENDING, next: true);
        break;

      case SubscriptionNotification::NOTIF_FAILED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_STOPPED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->end_date = now();
        $mockup->subscription->save();
        $mockup->updateInvoice(
          status: Invoice::STATUS_FAILED,
          next: true
        );
        break;

      case SubscriptionNotification::NOTIF_LAPSED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_FAILED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->end_date = now();
        $mockup->subscription->save();
        $mockup->updateInvoice(
          status: Invoice::STATUS_FAILED,
          next: true
        );
        break;

      case SubscriptionNotification::NOTIF_EXTENDED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->moveToNext();
        $mockup->subscription->fillNextInvoice();
        $mockup->subscription->save();
        $mockup->subscription->createRenewal();
        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_SOURCE_INVALID:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->moveToNext();
        $mockup->subscription->fillNextInvoice();
        $mockup->subscription->save();
        $mockup->subscription->createRenewal();
        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_ORDER_INVOICE:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_REFUNDED);
        $mockup->updateRefund(true);
        break;

      case SubscriptionNotification::NOTIF_TERMINATED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_STOPPED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->stop(Subscription::STATUS_STOPPED);
        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_TERMS_CHANGED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->updateInvoice(status: Invoice::STATUS_COMPLETED);
        break;

      case SubscriptionNotification::NOTIF_PLAN_UPDATED_GERMAN:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        break;

      case SubscriptionNotification::NOTIF_PLAN_UPDATED_OTHER:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        break;
    }
    return $mockup;
  }

  public function clean()
  {
    SubscriptionNotificationTest::clean();
    return "Cleaned!";
  }

  protected function validateNotificationRequest(Request $request, string $type)
  {
    $country = strtoupper($request->country ?? 'US');
    if (Country::findByCode($country) === null) {
      throw new HttpException(400, 'Country not found');
    }
    $plan = $request->plan ?? 'month';
    if (!in_array($plan, ['month', 'year'])) {
      throw new HttpException(400, 'Invalid Plan');
    }
    $coupon = $request->coupon;
    if ($coupon && !in_array($coupon, ['free-trial', 'percentage', 'percentage-fixed-term'])) {
      throw new HttpException(400, 'Invalid coupon');
    }
    $licenseCount = $request->license_count ?? 0;
    return ['country' => $country, 'plan' => $plan, 'coupon' => $coupon, 'licenseCount' => $licenseCount];
  }

  public function sendMail(Request $request, string $type)
  {
    $data = $this->validateNotificationRequest($request, $type);
    $mockup = $this->prepare($type, $data['country'], $data['plan'], $data['coupon']);
    if (!$mockup) {
      return response("Team Siser ... Skipped!");
    }

    $mockup->subscription->sendNotification($type, $mockup->invoice, [
      'refund' => $mockup->refund,
      'credit_memo' => '/credit-memo/robots.txt'
    ]);

    return response('Please checkout your email');
  }

  public function viewNotification(Request $request, string $type)
  {
    $data = $this->validateNotificationRequest($request, $type);
    $mockup = $this->prepare($type, $data['country'], $data['plan'], $data['coupon'], $data['licenseCount']);
    if (!$mockup) {
      return response("Team Siser ... Skipped!");
    }

    return (new SubscriptionNotification($type, [
      'subscription' => $mockup->subscription,
      'invoice' => $mockup->invoice,
      'refund' => $mockup->refund,
      'credit_memo' => '/credit-memo/robots.txt'
    ]))->toMail($mockup->user);
  }
}
