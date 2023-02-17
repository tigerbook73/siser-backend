<?php

namespace Tests\Feature;

use App\Models\Country;

class CountryListApiTest extends CountryTestCase
{
  public ?string $role = '';

  public function testCountryListSuccess()
  {
    // no filter
    $this->listAssert(200, []);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
