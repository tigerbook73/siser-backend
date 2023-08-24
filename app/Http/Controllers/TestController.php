<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Test\SubscriptionNotificationTest;
use App\Models\Country;

class TestController extends Controller
{
  public function resetData()
  {
    Artisan::call('db:seed', ['--force' => true]);

    return response()->json(['message' => 'test data reset successfully!']);
  }

  public function prepare(string $type, string $country, string $coupon = null)
  {
    $notificationConfigures = [
      SubscriptionNotification::NOTIF_ORDER_ABORTED => [
        'subscription_status'         => Subscription::STATUS_FAILED,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 0,
        'invoice_status'              => Invoice::STATUS_FAILED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_ORDER_CANCELLED => [
        'subscription_status'         => Subscription::STATUS_FAILED,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 0,
        'invoice_status'              => Invoice::STATUS_CANCELLED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_ORDER_REFUNDED => [
        'subscription_status'         => null,
        'subscription_sub_status'     => null,
        'subscription_current_period' => null,
        'invoice_status'              => Invoice::STATUS_REFUNDED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_ORDER_CONFIRMED => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_COMPLETED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_CANCELLED => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_CANCELLING,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_CANCELLED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_CANCELLED_REFUND => [
        'subscription_status'         => Subscription::STATUS_STOPPED,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_REFUNDING,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_REMINDER => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_OPEN,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_INVOICE_PENDING => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_PENDING,
        'invoice_period'              => 2,
      ],
      SubscriptionNotification::NOTIF_FAILED => [
        'subscription_status'         => Subscription::STATUS_STOPPED,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_FAILED,
        'invoice_period'              => 2,
      ],
      SubscriptionNotification::NOTIF_EXTENDED => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 2,
        'invoice_status'              => Invoice::STATUS_COMPLETED,
        'invoice_period'              => 2,
      ],
      SubscriptionNotification::NOTIF_ORDER_INVOICE => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_COMPLETED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_REFUNDED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_TERMINATED => [
        'subscription_status'         => Subscription::STATUS_STOPPED,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_COMPLETED,
        'invoice_period'              => 1,
      ],
      SubscriptionNotification::NOTIF_TERMS_CHANGED => [
        'subscription_status'         => Subscription::STATUS_ACTIVE,
        'subscription_sub_status'     => Subscription::SUB_STATUS_NORMAL,
        'subscription_current_period' => 1,
        'invoice_status'              => Invoice::STATUS_COMPLETED,
        'invoice_period'              => 1,
      ],
    ];;


    $mockup = SubscriptionNotificationTest::init($country);
    $config = $notificationConfigures[$type] ?? [];
    if (!empty($config)) {
      $mockup->updateSubscription(
        status: $config['subscription_status'],
        subStatus: $config['subscription_sub_status'],
        currentPeriod: $config['subscription_current_period']
      );
      $mockup->updateInvoice(
        status: $config['invoice_status'],
        period: $config['invoice_period']
      );
    }
    if ($coupon) {
      $mockup->updateCoupon($coupon);
      $mockup->updateSubscriptionCoupon();
      $mockup->updateInvoiceCoupon();
    }
    if (
      $type === SubscriptionNotification::NOTIF_ORDER_REFUNDED ||
      $type === SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO
    ) {
      $mockup->updateRefund(true);
    } else if (
      $type === SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED
    ) {
      $mockup->updateRefund(false);
    }

    return $mockup;
  }

  public function clean()
  {
    SubscriptionNotificationTest::clean();
    return "Cleaned!";
  }

  public function sendMail(Request $request, string $type)
  {
    $country = strtoupper($request->country ?? 'US');
    if ($country && Country::findByCode($country) === null) {
      return response('Country not found', 404);
    }
    $coupon = $request->coupon;
    if ($coupon && !in_array($request->coupon, ['free-trial', 'percentage-off'])) {
      return response("Invalid coupon", 400);
    }

    $mockup = $this->prepare($type, $country, $coupon);
    $mockup->subscription->sendNotification($type, $mockup->invoice);
    return response('Please checkout your email');
  }

  public function viewNotification(Request $request, string $type)
  {
    $country = strtoupper($request->country ?? 'US');
    if ($country && Country::findByCode($country) === null) {
      return response('Country not found', 404);
    }
    $coupon = $request->coupon;
    if ($coupon && !in_array($request->coupon, ['free-trial', 'percentage-off'])) {
      return response("Invalid coupon", 400);
    }

    $mockup = $this->prepare($type, $country, $coupon);
    return (new SubscriptionNotification($type, [
      'subscription' => $mockup->subscription,
      'invoice' => $mockup->invoice,
      'refund' => $mockup->refund,
    ]))->toMail($mockup->user);
  }
}
