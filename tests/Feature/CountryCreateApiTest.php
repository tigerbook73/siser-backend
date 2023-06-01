<?php

namespace Tests\Feature;

use App\Services\TimeZone;

class CountryCreateApiTest extends CountryTestCase
{
  public ?string $role = 'admin';

  public function testCountryCreateOk()
  {
    $response = $this->createAssert();
    $this->assertEquals($response->json('timezone'), TimeZone::default($this->modelCreate['code']));
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
