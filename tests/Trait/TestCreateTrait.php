<?php

namespace Tests\Trait;

trait TestCreateTrait
{
  public function createBillingInfo()
  {
    /**
     * mock up functions
     */
    if (isset($this->user->dr['customer_id'])) {
      $this->mockUpdateCustomer();
    } else {
      $this->mockCreateCustomer();
    }

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
    /**
     * mock up functions
     */
    $this->mockAttachCustomerSource();
    if ($this->user->payment_method->dr['source_id'] ?? null) {
      $this->mockDetachCustomerSourceAsync();
    }
    if ($activeSubscripiton = $this->user->getActiveLiveSubscription()) {
      $this->mockUpdateSubscriptionSource($activeSubscripiton);
    }

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
    /**
     * mock up functions
     */
    $this->mockCreateCheckout();

    $response = $this->postJson($this->baseUrl, $data);

    // refresh authenticated user data
    $this->user->refresh();

    return $response;
  }
}
