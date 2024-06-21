<?php

namespace Tests\Feature;

use App\Models\LicensePackage;

class LicensePackageCustomerListApiTest extends LicensePackageTestCase
{
  public ?string $role = 'customer';
  public string $baseUrl = '/api/v1/customer/license-packages';


  public function testLicensePackageCustomerListOk()
  {
    $this->listAssert();
    $this->listAssert(params: ['status' => LicensePackage::STATUS_ACTIVE]);
  }
}
