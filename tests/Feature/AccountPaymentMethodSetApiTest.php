<?php

namespace Tests\Feature;

class AccountPaymentMethodSetApiTest extends AccountPaymentMethodTestCase
{
  public ?string $role = 'customer';

  public function testAccountPaymentMethodSetOk()
  {
    $this->createBillingInfo();

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

    // update
    $response = $this->postJson("{$this->baseUrl}/payment-method", $this->modelUpdate);
    $response->assertSuccessful()
      ->assertJsonStructure($this->modelSchema)
      ->assertJson($this->modelUpdate);

    return $response;
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
