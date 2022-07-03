<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;

class SoftwarePackageListApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

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
