<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;

class SoftwarePackageCreateApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

  public function testSoftwarePackageCreateOk()
  {
    $this->createAssert();
  }

  public function testSoftwarePackageCreateMacOk()
  {
    $this->modelCreate['platform'] = "Mac";

    $this->createAssert();
  }

  public function testSoftwarePackageCreateError()
  {
    $modelCreate = $this->modelCreate;

    /**
     * error platform
     */
    $this->modeCreate = $modelCreate;
    unset($this->modelCreate['platform']);
    $this->createAssert(422);

    $this->modeCreate = $modelCreate;
    $this->modelCreate['platform'] = "abc";
    $this->createAssert(422);

    $this->modeCreate = $modelCreate;
    $this->modelCreate['platform'] = "";
    $this->createAssert(422);

    /**
     * error name
     */
    $this->modeCreate = $modelCreate;
    unset($this->modelCreate['name']);
    $this->createAssert(422);

    $this->modeCreate = $modelCreate;
    $this->modelCreate['name'] = "";
    $this->createAssert(422);

    /**
     * error version
     */
    $this->modeCreate = $modelCreate;
    unset($this->modelCreate['version']);
    $this->createAssert(422);

    $this->modeCreate = $modelCreate;
    $this->modelCreate['version'] = "";
    $this->createAssert(422);
  }
}
