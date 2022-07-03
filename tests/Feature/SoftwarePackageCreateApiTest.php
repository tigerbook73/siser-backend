<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;

class SoftwarePackageCreateApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

  public function testSoftwarePackageCreateOk()
  {
    $this->createAssert();
  }

  // TODO: more tests to come
}
