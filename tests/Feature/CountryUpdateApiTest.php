<?php

namespace Tests\Feature;

class CountryUpdateApiTest extends CountryTestCase
{

  public ?string $role = 'admin';

  public function testCountryUpdateOk()
  {
    $this->updateAssert(200, $this->object->code);
  }

  public function testMore()
  {
    $this->markTestIncomplete('more test cases to come');
  }
}
