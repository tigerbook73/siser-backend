<?php

namespace Tests\Feature;

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

  // TODO: more test cases needed
  // + remove latest software packages (latest need to updated)
  // + remove non-latest software packages (latest need not to be updated)


  public function testSoftwarePackageDeleteNok()
  {
    $response = $this->deleteJson("$this->baseUrl/" . 999999);
    $response->assertStatus(404);
  }
}
