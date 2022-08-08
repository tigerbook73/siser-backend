<?php

namespace Tests\Feature;

class SoftwarePackageListApiTest extends SoftwarePackageTestCase
{
  public ?string $role = '';

  public function testSoftwarePackageListSuccess()
  {
    $this->listAssert(
      200,
      ['platform' => 'Windows'],
    );

    $this->listAssert(
      200,
      ['platform' => 'Mac'],
    );

    $this->listAssert(
      200,
      [],
    );

    $this->listAssert();

    $this->listAssert(
      200,
      ['platform' => 'Windows'],
      1,
    );
  }

  public function testSoftwarePackageListError()
  {
    $this->listAssert(
      422,
      ['platform' => ''],
    );

    $this->listAssert(
      422,
      ['platform' => 'Linux'],
    );

    $this->listAssert(
      400,
      ['platform' => 'Windows', 'version_type' => 'stable'],
    );

    $this->listAssert(
      400,
      ['platform' => 'Windows', 'version' => '5.0.1'],
    );

    $this->listAssert(
      400,
      ['version_type' => 'stable'],
    );

    $this->listAssert(
      400,
      ['version' => '5.0.1'],
    );
  }
}
