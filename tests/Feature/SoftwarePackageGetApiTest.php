<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;

class SoftwarePackageGetApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

  public function testSoftwarePackageGetOk()
  {
    $this->getAssert(200, 1);
  }
}
