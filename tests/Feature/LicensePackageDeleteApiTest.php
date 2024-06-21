<?php

namespace Tests\Feature;

use App\Models\LicensePackage;

class LicensePackageDeleteApiTest extends LicensePackageTestCase
{
  public ?string $role = 'admin';

  public function testLicensePackageDeleteOk()
  {
    $id = LicensePackage::first()->id;
    $response = $this->deleteJson("$this->baseUrl/" . $id);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('license_packages', [
      'id' => $id,
    ]);
  }
}
