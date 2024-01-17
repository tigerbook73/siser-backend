<?php

namespace Tests\Feature\Full;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use Carbon\Carbon;
use Tests\DR\DrApiTestCase;

class DrFreeTrialSubscriptionTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * the following is subscription path
   */
  public function init_free_trial()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription(Plan::INTERVAL_MONTH, Coupon::DISCOUNT_TYPE_FREE_TRIAL);
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccept(Subscription::find($response->json('id')));
    return $subscription;
  }

  public function test_normal_procedure()
  {
    $subscription = $this->init_free_trial();

    $this->onOrderComplete($subscription);
    $this->onSubscriptionReminder($subscription);
    $this->onSubscriptionPaymentFailed($subscription);
    $this->onSubscriptionExtended($subscription);
  }
}
