<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;

class SoftwarePackageUpdateApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

  public function testSoftwarePackageUpdateOk()
  {
    $this->updateAssert(200, 1);
  }

  // TODO: more tests to come
}
