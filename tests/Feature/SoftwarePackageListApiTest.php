<?php

namespace Tests\Feature;

class SoftwarePackageListApiTest extends SoftwarePackageTestCase
{
  public ?string $role = '';

  public function testSoftwarePackageListSuccess()
  {
    $this->listAssert(200, ['platform' => 'Windows']);

    $this->listAssert(200, ['platform' => 'Mac']);

    $this->listAssert(200, []);

    $this->listAssert();

    $this->listAssert(200, ['platform' => 'Windows'], 1);
  }

  public function testSoftwarePackageListError()
  {
    $response = $this->listAssert(422, ['platform' => ''],);
    $response->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    $response = $this->listAssert(422, ['platform' => 'Linux'],);
    $response->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    $this->listAssert(400, ['platform' => 'Windows', 'version_type' => 'stable']);

    $this->listAssert(400, ['platform' => 'Windows', 'version' => '5.0.1']);

    $this->listAssert(400, ['version_type' => 'stable']);

    $this->listAssert(400, ['version' => '5.0.1']);
  }
}
