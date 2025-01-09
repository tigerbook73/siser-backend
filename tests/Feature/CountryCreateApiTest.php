<?php

namespace Tests\Feature;

class CountryCreateApiTest extends CountryTestCase
{
  public ?string $role = 'admin';

  public function testCountryCreateOk()
  {
    $this->createAssert();
  }
}
