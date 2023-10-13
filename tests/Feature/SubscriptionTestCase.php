<?php

namespace Tests\Feature;

use App\Models\Subscription;
use Tests\ApiTestCase;
use Tests\Models\BillingInfo;
use Tests\Models\PlanInfo;
use Tests\Models\Subscription as ModelsSubscription;

class SubscriptionTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/subscriptions';
  public string $model = Subscription::class;


  public Subscription $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsSubscription);
    $this->modelSchema['billing_info'] = array_keys((array)new BillingInfo);
    $this->modelSchema['plan_info'] = array_keys((array)new PlanInfo);
    // $this->modelSchema['coupon_info'] = array_keys((array)new Coupon);

    $this->modelUpdate = [];
  }
}
