<?php

namespace Tests\Feature;

use App\Models\Base\Country;

class CountryDeleteApiTest extends CountryTestCase
{
  public ?string $role = 'admin';

  public function testCountryDeleteOk()
  {
    $response = $this->deleteJson("$this->baseUrl/" . $this->object->code);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('countries', [
      'code' => $this->object->code,
    ]);
  }

  public function testMoreTestCases()
  {
    $this->markTestIncomplete('more test cases needed');
  }
}
