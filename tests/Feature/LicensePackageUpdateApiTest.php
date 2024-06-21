<?php

namespace Tests\Feature;

class LicensePackageUpdateApiTest extends LicensePackageTestCase
{
  public ?string $role = 'admin';

  public function testLicensePackageUpdateOk()
  {
    $this->updateAssert(200, $this->object->id);
  }

  public function testLicensePackageUpdateNok()
  {
    $this->updateAssert(404, id: 99999999);
  }
}
