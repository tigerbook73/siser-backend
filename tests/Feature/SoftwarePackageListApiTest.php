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
    $this->listAssert(200, ['name' => $this->createRandomString(255)]);

    // platform
    $this->listAssert(200, ['platform' => 'Windows']);
    $this->listAssert(200, ['platform' => 'Mac']);
    $this->listAssert(200, ['platform' => $this->object->platform]);

    // version type
    $this->listAssert(200, ['version_type' => 'stable']);
    $this->listAssert(200, ['version_type' => 'beta']);
    $this->listAssert(200, ['version_type' => $this->object->version_type]);

    // version
    $this->listAssert(200, ['version' => '1.1.2']);
    $this->listAssert(200, ['version' => '1.1.2.3.beta']);
    $this->listAssert(200, ['version' => $this->object->version]);
    $this->listAssert(200, ['version' => 'latest'], SoftwarePackageLatest::count());

    // Combinations
    $this->listAssert(200, ['platform' => 'Windows', 'version_type' => 'stable']);
    $this->listAssert(200, ['platform' => 'Windows', 'version' => '0.0.1']);
    $this->listAssert(200, ['platform' => 'Windows', 'version_type' => 'stable', 'version' => '0.0.1']);
  }

  public function testSoftwarePackageListError()
  {
    $response = $this->listAssert(422, ['platform' => ''],);
    $response->assertJsonValidationErrors(['platform' => 'The platform field must have a value.']);

    $response = $this->listAssert(422, ['platform' => 'Linux'],);
    $response->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    $this->listAssert(422, ['platform' => 'Win', 'version_type' => 'beta'])->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    $this->listAssert(422, ['version_type' => ''],)->assertJsonValidationErrors(['version_type' => 'The version type field must have a value.']);

    $this->listAssert(422, ['version_type' => 'ok'])->assertJsonValidationErrors(['version_type' => 'The selected version type is invalid.']);

    $this->listAssert(422, ['platform' => 'Windows', 'version_type' => 'ok'])->assertJsonValidationErrors(['version_type' => 'The selected version type is invalid.']);

    $this->listAssert(422, ['name' => ''],)->assertJsonValidationErrors(['name' => 'The name field must have a value.']);

    $this->listAssert(422, ['version' => ''],)->assertJsonValidationErrors(['version' => 'The version field must have a value.']);
  }
}
