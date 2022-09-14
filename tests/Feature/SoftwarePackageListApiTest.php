<?php

namespace Tests\Feature;

use App\Models\Base\SoftwarePackageLatest;

class SoftwarePackageListApiTest extends SoftwarePackageTestCase
{
  public ?string $role = '';

  public function testSoftwarePackageListSuccess()
  {
    // no filter
    $this->listAssert(200, []);

    // name
    $this->listAssert(200, ['name' => $this->object->name]);
    // TODO: more

    // platform
    $this->listAssert(200, ['platform' => 'Windows']);
    $this->listAssert(200, ['platform' => 'Mac']);

    // version type 
    $this->listAssert(200, ['version_type' => 'stable']);
    $this->listAssert(200, ['version_type' => 'beta']);
    $this->listAssert(200, ['version_type' => $this->object->version_type]);
    // TODO: more

    // version
    $this->listAssert(200, ['version' => $this->object->version]);
    $this->listAssert(200, ['version' => 'latest'], SoftwarePackageLatest::count());
  }

  public function testSoftwarePackageListError()
  {
    $response = $this->listAssert(422, ['platform' => ''],);
    $response->assertJsonValidationErrors(['platform' => 'The platform field must have a value.']);

    $response = $this->listAssert(422, ['platform' => 'Linux'],);
    $response->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    // $this->listAssert(400, ['platform' => 'Windows', 'version_type' => 'stable']);
    // $this->listAssert(400, ['platform' => 'Windows', 'version' => '5.0.1']);
    // $this->listAssert(400, ['version_type' => 'stable']);
    // TODO: more
  }
}
