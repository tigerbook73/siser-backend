<?php

namespace Tests\Feature;

use App\Models\User;

class CouponCreateApiTest extends CouponTestCase
{
  public ?string $role = 'admin';

  public function testCouponCreateOk()
  {
    $this->createAssert();
  }

  public function testCouponCreateValidCountriesOk1()
  {

    $countries = ['CA', 'US'];
    $this->modelCreate['condition']['countries'] = $countries;
    $this->createAssert();
  }

  protected function createOKTest(array $countries)
  {
    $this->modelCreate['condition']['countries'] = $countries;
    $this->noAssert = true;
    $response = $this->createAssert();

    $this->modelCreate['condition']['countries'] = collect($countries)->sort()->unique()->values()->toArray();
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertJson(array_diff_key($this->modelCreate, array_flip($this->hidden)));

    return $response;
  }

  public function testCouponCreateValidCountriesOk2()
  {
    $countries = ['US', 'CA'];
    $this->createOKTest($countries);
  }

  public function testCouponCreateValidCountriesOk3()
  {
    $countries = ['US', 'CA', 'CA'];
    $this->createOKTest($countries);
  }

  public function testCouponCreateInvalidCountriesNok()
  {
    $this->modelCreate['condition']['countries'] = ['SU', 'CA'];
    $this->createAssert(400);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
