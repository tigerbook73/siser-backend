<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;

class SoftwarePackageListApiTest extends SoftwarePackageTestCase
{
  public ?string $role = '';

  public function testSoftwarePackageListAll()
  {
    $this->listAssert();
  }

  public function testSoftwarePackageListFilter()
  {
    // 
    $this->listAssert(
      200,
      ['platform' => $this->object->platform],
    );
  }
}
