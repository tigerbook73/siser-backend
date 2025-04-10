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
  ): ?SubscriptionNotificationTest {
    $mockup = SubscriptionNotificationTest::init($country, $plan);

    switch ($type) {
      case SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_RENEW:
      case SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_FAILED:
        $mockup->updateSubscription(
          status: Subscription::STATUS_STOPPED,
          subStatus: Subscription::SUB_STATUS_NORMAL,
          currentPeriod: 1,
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

  protected function validateNotificationRequest(Request $request)
  {
    $country = strtoupper($request->country ?? 'US');
    if (Country::findByCode($country) === null) {
      throw new HttpException(400, 'Country not found');
    }
    $plan = $request->plan ?? 'month';
    if (!in_array($plan, ['month', 'year'])) {
      throw new HttpException(400, 'Invalid Plan');
    }
    return [
      'country'       => $country,
      'plan'          => $plan,
      'invoiceType'   => $request->invoice_type ?? Invoice::TYPE_NEW_SUBSCRIPTION,
      'immediate'     => ($request->immediate == 'true'),
    ];
  }

  public function sendMail(Request $request, string $type)
  {
    $data = $this->validateNotificationRequest($request);
    $mockup = $this->prepare(
      $type,
      $data['country'],
      $data['plan'],
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
    $data = $this->validateNotificationRequest($request);
    $mockup = $this->prepare(
      $type,
      $data['country'],
      $data['plan'],
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
