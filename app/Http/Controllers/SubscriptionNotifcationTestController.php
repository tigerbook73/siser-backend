<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use Illuminate\Http\Request;
use App\Http\Controllers\Test\SubscriptionNotificationTest;
use App\Models\Country;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionNotifcationTestController extends Controller
{
  public function prepare(
    string $type,
    string $country,
    string $plan,
    string $coupon = null,
    int $licenseCount = 0,
  ): SubscriptionNotificationTest|null {
    $mockup = SubscriptionNotificationTest::init($country, $plan);

    // skip invalid free-trial scenario
    if (
      in_array($type, [
        SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_RENEW,
        SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_FAILED,
      ])
      && $coupon == 'free-trial'
    ) {
      return null;
    }

    // skip license scenario if license count is 0
    if (str_starts_with($type, 'subscription.license.') && $licenseCount == 0) {
      return null;
    }

    if ($coupon) {
      $mockup->updateCoupon($coupon);
    }

    switch ($type) {
      case SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_RENEW:
      case SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_FAILED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_STOPPED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
          licenseCount: $licenseCount
        );
        $mockup->subscription->end_date = now();
        $mockup->subscription->save();
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
