<?php

namespace Tests\Feature;

class SoftwarePackageGetApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

  public function testSoftwarePackageGetSuccess()
  {
    $this->getAssert(200, $this->object->id);

    $this->getAssert(200, $this->object->id, ['version_type' => 'stable1']);

    $this->getAssert(200, $this->object->id, ['version_type' => 'beta']);

    $this->getAssert(200, $this->object->id, ['version' => '0.0.1']);

    $this->getAssert(200, $this->object->id, ['version' => '9.9.1']);

    $this->getAssert(200, $this->object->id, ['status' => 'all']);
  }

  public function testSoftwarePackageGetError()
  {
    $this->getAssert(404, 999999999999999999);

    $this->getAssert(404, -1);

    $this->getAssert(404, -209, ['version' => '9.9.1']);

    $this->getAssert(404, 0);
  }
}
