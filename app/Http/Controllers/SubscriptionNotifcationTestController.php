<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Test\DigitalRiverServiceTest;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Test\SubscriptionNotificationTest;
use App\Models\Country;
use App\Models\SubscriptionRenewal;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use App\Services\DigitalRiver\SubscriptionManagerResult;
use App\Services\LicenseSharing\LicenseSharingService;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionNotifcationTestController extends Controller
{
  public SubscriptionManager $manager;

  public function __construct()
  {
    $this->manager = new SubscriptionManagerDR(
      new DigitalRiverServiceTest(),
      new LicenseSharingService(),
      new SubscriptionManagerResult()
    );
  }

  public function prepare(
    string $type,
    string $country,
    string $plan,
    string $coupon = null,
    int $licenseCount = 0,
    string $invoiceType = Invoice::TYPE_NEW_SUBSCRIPTION,
    bool $immediate = false
  ): SubscriptionNotificationTest|null {
    $mockup = SubscriptionNotificationTest::init($country, $plan, $coupon);

    // skip invalid free-trial scenario
    if (
      in_array($type, [
        SubscriptionNotification::NOTIF_CANCELLED,
        SubscriptionNotification::NOTIF_CANCELLED_REFUND,
        SubscriptionNotification::NOTIF_EXTENDED,
        SubscriptionNotification::NOTIF_FAILED,
        SubscriptionNotification::NOTIF_INVOICE_PENDING,
        SubscriptionNotification::NOTIF_LAPSED,
        SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO,
        SubscriptionNotification::NOTIF_ORDER_INVOICE,
        SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED,
        SubscriptionNotification::NOTIF_ORDER_REFUNDED,
        SubscriptionNotification::NOTIF_RENEW_EXPIRED,
        SubscriptionNotification::NOTIF_RENEW_REQ_CONFIRMED,
        SubscriptionNotification::NOTIF_RENEW_REQUIRED,
        SubscriptionNotification::NOTIF_SOURCE_INVALID,

        SubscriptionNotification::NOTIF_LICENSE_CANCELLED,
        SubscriptionNotification::NOTIF_LICENSE_CANCELLED_REFUND,
        SubscriptionNotification::NOTIF_LICENSE_ORDER_CREDIT_MEMO,
        SubscriptionNotification::NOTIF_LICENSE_ORDER_INVOICE,
        SubscriptionNotification::NOTIF_LICENSE_ORDER_REFUND_FAILED,
        SubscriptionNotification::NOTIF_LICENSE_ORDER_REFUNDED,
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

    // skip license scenario if license count is 0
    if (str_starts_with($type, 'subscription.license.') && $licenseCount == 0) {
      return null;
    }

    // skip license scenario if license count is <= 1
    if ($type == SubscriptionNotification::NOTIF_LICENSE_DECREASE && $licenseCount <= 1) {
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
      case SubscriptionNotification::NOTIF_CANCELLED_IMMEDIATE:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_CANCELLING,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        if ($mockup->subscription->isFreeTrial() || $immediate) {
          $this->manager->stopSubscription($mockup->subscription, 'cancelled');
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
        $this->manager->stopSubscription($mockup->subscription, 'cancelled and refunded');
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
        $this->manager->stopSubscription($mockup->subscription, 'cancelled');
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

      case SubscriptionNotification::NOTIF_LICENSE_ORDER_CONFIRMED:
      case SubscriptionNotification::NOTIF_LICENSE_ORDER_INVOICE:
      case SubscriptionNotification::NOTIF_LICENSE_ORDER_CREDIT_MEMO:
      case SubscriptionNotification::NOTIF_LICENSE_ORDER_REFUNDED:
      case SubscriptionNotification::NOTIF_LICENSE_ORDER_REFUND_FAILED:
        if ($invoiceType == Invoice::TYPE_NEW_LICENSE_PACKAGE) {
          $mockup->updateSubscription(
            status: Subscription::STATUS_ACTIVE,
            subStatus: Subscription::SUB_STATUS_NORMAL,
            currentPeriod: 1,
            licenseCount: 0
          );
          $mockup->createLicenseInvoice(Invoice::TYPE_NEW_LICENSE_PACKAGE, $licenseCount);
          $mockup->updateSubscription(
            status: Subscription::STATUS_ACTIVE,
            subStatus: Subscription::SUB_STATUS_NORMAL,
            currentPeriod: 1,
            licenseCount: $licenseCount
          );
          $mockup->invoice->setStatus(Invoice::STATUS_COMPLETED);
        } else {
          $mockup->updateSubscription(
            status: Subscription::STATUS_ACTIVE,
            subStatus: Subscription::SUB_STATUS_NORMAL,
            currentPeriod: 1,
            licenseCount: $licenseCount
          );
          $mockup->createLicenseInvoice(Invoice::TYPE_INCREASE_LICENSE, $licenseCount + 1);
          $mockup->updateSubscription(
            status: Subscription::STATUS_ACTIVE,
            subStatus: Subscription::SUB_STATUS_NORMAL,
            currentPeriod: 1,
            licenseCount: $licenseCount + 1
          );
          $mockup->invoice->setStatus(Invoice::STATUS_COMPLETED);
        }

        if ($type == SubscriptionNotification::NOTIF_LICENSE_ORDER_REFUNDED) {
          $mockup->invoice->setStatus(Invoice::STATUS_REFUNDED);
          $mockup->updateRefund(true);
        } elseif ($type == SubscriptionNotification::NOTIF_LICENSE_ORDER_REFUND_FAILED) {
          $mockup->invoice->setStatus(Invoice::STATUS_REFUND_FAILED);
          $mockup->updateRefund(false);
        }

        break;

      case SubscriptionNotification::NOTIF_LICENSE_CANCELLED:
      case SubscriptionNotification::NOTIF_LICENSE_CANCELLED_IMMEDIATE:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $this->manager->cancelLicensePackage($mockup->subscription, $immediate);
        break;

      case SubscriptionNotification::NOTIF_LICENSE_CANCELLED_REFUND:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $this->manager->cancelLicensePackage($mockup->subscription, immediate: true);
        break;

      case SubscriptionNotification::NOTIF_LICENSE_DECREASE:
        $mockup->updateSubscription(
          status: Subscription::STATUS_ACTIVE,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $this->manager->decreaseLicenseNumber($mockup->subscription, $licenseCount - 1, $immediate);
        break;

      default:
        if (isset(SubscriptionNotification::$types[$type])) {
          throw new HttpException(400, "SubscriptionNotification type $type not implemented!");
        }

        return null;
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
    return [
      'country'       => $country,
      'plan'          => $plan,
      'coupon'        => $coupon,
      'licenseCount'  => $licenseCount,
      'invoiceType'   => $request->invoice_type ?? Invoice::TYPE_NEW_SUBSCRIPTION,
      'immediate'     => ($request->immediate == 'true'),
    ];
  }

  public function sendMail(Request $request, string $type)
  {
    $data = $this->validateNotificationRequest($request, $type);
    $mockup = $this->prepare(
      $type,
      $data['country'],
      $data['plan'],
      $data['coupon'],
      $data['licenseCount'],
      $data['invoiceType'],
      $data['immediate']
    );
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
    $mockup = $this->prepare(
      $type,
      $data['country'],
      $data['plan'],
      $data['coupon'],
      $data['licenseCount'],
      $data['invoiceType'],
      $data['immediate']
    );
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
