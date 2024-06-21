<?php

namespace Tests\Feature;

use App\Models\LicensePackage;

class LicensePackageListApiTest extends LicensePackageTestCase
{
  public ?string $role = 'admin';

  public function testLicensePackageListOk()
  {
    $this->modelCreate['type']    = LicensePackage::TYPE_EDUCATION;
    $this->modelCreate['status']  = LicensePackage::STATUS_INACTIVE;
    $this->createAssert();

    $this->listAssert();
    $this->listAssert(params: ['type' => $this->object->type]);
    $this->listAssert(params: ['type' => LicensePackage::TYPE_STANDARD]);
    $this->listAssert(params: ['type' => LicensePackage::TYPE_EDUCATION]);
    $this->listAssert(params: ['type' => 'null']);
    $this->listAssert(params: ['name' => $this->object->name]);
    $this->listAssert(params: ['name' => 'null']);
    $this->listAssert(params: ['status' => $this->object->status]);
    $this->listAssert(params: ['status' => LicensePackage::STATUS_ACTIVE]);
    $this->listAssert(params: ['status' => LicensePackage::STATUS_INACTIVE]);
    $this->listAssert(params: ['status' => 'null']);
  }
}
