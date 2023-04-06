<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use Tests\ApiTestCase;
use Tests\Models\Subscription as ModelsSubscription;

class AccountSubscriptionTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/account/subscriptions';
  public string $model = Subscription::class;

  public Subscription $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsSubscription);
  }

  public function createBillingInfo()
  {
    $response = $this->postJson('/api/v1/account/billing-info', [
      'first_name'    => 'first_name',
      'last_name'     => 'last_name',
      'phone'         => '',
      'organization'  => '',
      'email'         => 'test-case@me.com',
      'address' => [
        'line1'       => '328 Reserve Road,  VIC',
        'line2'       => '',
        'city'        => 'Cheltenham',
        'postcode'    => '3192',
        'state'       => 'VIC',
        'country'     => 'AU',
      ]
    ]);

    // refresh authenticated user data
    $this->user->refresh();

    return $response;
  }

  public function createPaymentMethod()
  {
    $response = $this->postJson('/api/v1/account/payment-method', [
      'type' => 'creditCard',
      'dr' => ['source_id' => 'digital-river-source-id-master'],
    ]);

    // refresh authenticated user data
    $this->user->refresh();

    return $response;
  }

  public function createSubscription($data)
  {
    $response = $this->postJson($this->baseUrl, $data);

    // refresh authenticated user data
    $this->user->refresh();

    return $response;
  }
}
