<?php

namespace Tests\Feature;

use App\Models\Base\SoftwarePackage;
use App\Models\Base\SoftwarePackageLatest;

class SoftwarePackageDeleteApiTest extends SoftwarePackageTestCase
{

  public ?string $role = 'admin';

  public function testSoftwarePackageDeleteOk()
  {
    $response = $this->deleteJson("$this->baseUrl/" . $this->object->id);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('software_packages', [
      'id' => $this->object->id,
    ]);
    $this->assertDatabaseMissing('software_package_latests', [
      'software_package_id' => $this->object->id,
    ]);
  }

  public function testSoftwarePackageLatestDeleteOk()
  {
    $softwarePackageLatestOriginal = SoftwarePackageLatest::get('software_package_id')->toArray();

    $this->modelCreate['name'] = 'LDS Software';
    $this->modelCreate['platform'] = 'Windows';
    $this->modelCreate['version'] = '101.99.99';
    $this->createAssert();

    // Prove latest software packages have changes
    $this->assertTrue(count($softwarePackageLatestOriginal) !== SoftwarePackageLatest::whereIn('software_package_id', $softwarePackageLatestOriginal)->get()->count());
    $this->assertTrue(count($softwarePackageLatestOriginal) === SoftwarePackageLatest::get()->count());

    $softwarePackageLatest = SoftwarePackageLatest::where('platform', 'Windows')->first();

    $response = $this->deleteJson("$this->baseUrl/" . $softwarePackageLatest->software_package_id);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('software_packages', [
      'id' => $softwarePackageLatest->software_package_id,
    ]);

    $this->assertDatabaseMissing('software_package_latests', [
      'software_package_id' => $softwarePackageLatest->software_package_id,
    ]);

    // Prove latest software packages back to no changes
    $this->assertTrue(count($softwarePackageLatestOriginal) === SoftwarePackageLatest::whereIn('software_package_id', $softwarePackageLatestOriginal)->get()->count());
    $this->assertTrue(count($softwarePackageLatestOriginal) === SoftwarePackageLatest::get()->count());
  }

  public function testSoftwarePackageNotLatestDeleteOk()
  {
    $this->modelCreate['name'] = 'LDS Software';
    $this->modelCreate['version'] = '100.99.99';
    $this->createAssert();

    $softwarePackageLatest = SoftwarePackageLatest::get("software_package_id")->toArray();
    $softwarePackage = SoftwarePackage::whereNotIn('id', $softwarePackageLatest)->first();

    $response = $this->deleteJson("$this->baseUrl/" . $softwarePackage->id);
    $response->assertStatus(200);

    $this->assertDatabaseMissing('software_packages', [
      'id' => $softwarePackage->id,
    ]);
    $this->assertDatabaseMissing('software_package_latests', [
      'software_package_id' => $softwarePackage->id,
    ]);

    // Prove latest software packages no changes
    $this->assertTrue(count($softwarePackageLatest) === SoftwarePackageLatest::whereIn('software_package_id', $softwarePackageLatest)->get()->count());
    $this->assertTrue(count($softwarePackageLatest) === SoftwarePackageLatest::get()->count());
  }

  public function testSoftwarePackageDeleteNok()
  {
    $response = $this->deleteJson("$this->baseUrl/" . 999999);
    $response->assertStatus(404);
  }
}
