<?php

namespace Tests\Feature;

class AccountBillingInfoSetApiTest extends AccountBillingInfoTestCase
{
  public ?string $role = 'customer';

  public function testSetOk()
  {
    $response = $this->createAssert(200);
    return $response;
  }
}
