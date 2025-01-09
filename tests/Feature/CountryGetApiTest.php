<?php

namespace Tests\Feature;

class CountryGetApiTest extends CountryTestCase
{
  public ?string $role = 'admin';

  public function testCountryGetSuccess()
  {
    $this->getAssert(200, 'US');

    $this->getAssert(200, 'AU');
  }
}
