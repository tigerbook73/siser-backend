<?php

namespace Tests\Feature;

use App\Models\Base\Country;

class CountryDeleteApiTest extends CountryTestCase
{
  public ?string $role = 'admin';

  public function testCountryDeleteOk()
  {
    $code = $this->createAssert()->json()['code'];
    $response = $this->deleteJson("$this->baseUrl/" . $code);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('countries', [
      'code' => $code,
    ]);
  }

  public function testCountryDeleteNok()
  {
    $response = $this->deleteJson("$this->baseUrl/" . $this->object->code);
    $response->assertStatus(422);
  }
}
