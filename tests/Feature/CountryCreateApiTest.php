<?php

namespace Tests\Feature;

class CountryCreateApiTest extends CountryTestCase
{
  public ?string $role = 'admin';

  public function testCountryCreateOk()
  {
    $this->createAssert();
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
