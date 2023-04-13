<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use Illuminate\Support\Facades\Artisan;

class TestController extends Controller
{
  public function resetData()
  {
    Artisan::call('db:seed', ['--force' => true]);

    return response()->json(['message' => 'test data reset successfully!']);
  }

  public function sendMail(string $type)
  {
    /** @var Subscription|null $subscription */
    $subscription = Subscription::where('status', 'active')->first();
    if (!$subscription) {
      return response('No test subscription, please create one active subscription first', 400);
    }

    $invoice = $subscription->invoices()->orderBy('id', 'desc')->first();
    if ($type == 'invoice-pdf' && $invoice == null) {
      return response('No invoice, please try to generate invoice before test', 400);
    }

    $subscription->sendNotification(preg_replace('/-/', '.', $type, 1), $invoice);

    return response('Please checkout your email');
  }

  public function viewNotification(string $type)
  {
    $subscription = Subscription::where('status', 'active')->first();
    if (!$subscription) {
      return response('No test subscription, please create one active subscription first', 400);
    }

    $invoice = $subscription->invoices()->orderBy('id', 'desc')->first();
    if ($type == 'invoice-pdf' && $invoice == null) {
      return response('No invoice, please try to generate invoice before test', 400);
    }

    return (new SubscriptionNotification(preg_replace('/-/', '.', $type, 1), $subscription, $invoice))->toMail(User::first());
  }
}
