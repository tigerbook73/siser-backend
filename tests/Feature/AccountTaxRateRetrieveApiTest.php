<?php

namespace Tests\Feature;

use Tests\DR\DrApiTestCase;

class AccountTaxRateRetrieveApiTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  public function testRetrieveTaxRateOK()
  {
    $this->createOrUpdateBillingInfo();
    $this->retrieveTaxRate();
  }

  public function testRetrieveTaxRateWithTaxIdOK()
  {
    $this->createOrUpdateBillingInfo();
    $taxId = $this->createTaxId();
    $this->retrieveTaxRate($taxId->id);
  }
}
