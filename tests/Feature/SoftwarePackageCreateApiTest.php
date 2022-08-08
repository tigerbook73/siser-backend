<?php

namespace Tests\Feature;

class SoftwarePackageCreateApiTest extends SoftwarePackageTestCase
{
  public ?string $role = 'admin';

  public function testSoftwarePackageCreateOk()
  {
    $this->createAssert();
  }

  public function testSoftwarePackageCreateSuccess()
  {
    $modelCreate = $this->modelCreate;

    /**
     * success name
     */
    $this->modelCreate['name'] = 'Launch day';
    $this->createAssert();

    $this->modelCreate['name'] = $this->createRandomString(255);
    $this->createAssert();

    /**
     * success platform
     */
    $this->modelCreate['platform'] = 'Mac';
    $this->createAssert();

    $this->modelCreate['platform'] = 'Windows';
    $this->createAssert();

    /**
     * success version
     */
    $this->modelCreate['version'] = '1.0.1';
    $this->createAssert();

    $this->modelCreate['version'] = $this->createRandomString(255);
    $this->createAssert();

    /**
     * success version type
     */
    $this->modelCreate['version_type'] = 'stable';
    $this->createAssert();

    $this->modelCreate['version_type'] = 'beta';
    $this->createAssert();

    /**
     * success released date
     */
    $this->modelCreate['released_date'] = '2022-08-04 00:00:00';
    $this->createAssert();

    /**
     * success filename
     */
    $this->modelCreate['filename'] = 'happy_driver_2022';
    $this->createAssert();

    $this->modelCreate['filename'] = $this->createRandomString(255);
    $this->createAssert();

    /**
     * success url
     */
    $this->modelCreate['url'] = 'https://st-software.siser.com/software-packages/download?version=xkxalk12312ljj';
    $this->createAssert();

    $this->modelCreate['url'] = $this->createRandomString(255);
    $this->createAssert();

    /**
     * success description
     */
    $this->modelCreate['description'] = 'Lenarodo Design Studio 6.0.1 for Mac';
    $this->createAssert();

    unset($this->modelCreate['description']);
    $this->createAssert();

    $this->modelCreate = $modelCreate;
    $this->modelCreate['description'] = $this->createRandomString(255);
    $this->createAssert();

    /**
     * success release notes
     */
    $this->modelCreate = $modelCreate;
    $this->modelCreate['release_notes'] = 'https://st-software.siser.com/software-packages/lds/6.0.1/release_notes';
    $this->createAssert();

    unset($this->modelCreate['release_notes']);
    $this->createAssert();

    $this->modelCreate = $modelCreate;
    $this->modelCreate['release_notes'] = $this->createRandomString(255);
    $this->createAssert();
  }

  public function testSoftwarePackageCreateReleasedDateSuccess()
  {
    $modelCreate = $this->modelCreate;

    /**
     * success released date
     */
    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '31-12-2022 00:00:00';
    $response = $this->createAssert();
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 00:00:00');

    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '31-12-2022 15:43:31';
    $response = $this->createAssert();
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 15:43:31');

    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '31-12-2022';
    $response = $this->createAssert();
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 00:00:00');

    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-12-31';
    $response = $this->createAssert();
    $response->assertStatus(201)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 00:00:00');
  }

  public function testSoftwarePackageCreateError()
  {
    $modelCreate = $this->modelCreate;

    /**
     * error name
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['name']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['name'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['name'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error platform
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['platform']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['platform'] = 'abc';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['platform'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['platform'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error version
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['version']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['version'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['version'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error version type
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['version_type']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['version_type'] = 'xxx';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['version_type'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['version_type'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error released date
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['released_date']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = 'xxx';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-08-04 29:26:03';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-13-31 29:26:03';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-11-40 16:26:03';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error filename
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['filename']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['filename'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['filename'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error url
     */
    $this->modelCreate = $modelCreate;
    unset($this->modelCreate['url']);
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['url'] = '';
    $this->createAssert(422);

    $this->modelCreate = $modelCreate;
    $this->modelCreate['url'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error description
     */
    $this->modelCreate = $modelCreate;
    $this->modelCreate['description'] = $this->createRandomString(256);
    $this->createAssert(422);

    /**
     * error release notes
     */
    $this->modelCreate = $modelCreate;
    $this->modelCreate['release_notes'] = $this->createRandomString(256);
    $this->createAssert(422);
  }

  public function testSoftwarePackageCreateReleasedDateError()
  {
    /**
     * error released date
     */
    $modelCreate = $this->modelCreate;
    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-12-33';
    $response = $this->createAssert();
    $response->assertStatus(422);

    $modelCreate = $this->modelCreate;
    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-13-25';
    $response = $this->createAssert();
    $response->assertStatus(422);

    $modelCreate = $this->modelCreate;
    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-13-25 14:09:30';
    $response = $this->createAssert();
    $response->assertStatus(422);

    $modelCreate = $this->modelCreate;
    $this->noAssert = TRUE;
    $this->modelCreate = $modelCreate;
    $this->modelCreate['released_date'] = '2022-12-25 14:78:29';
    $response = $this->createAssert();
    $response->assertStatus(422);
  }
}
