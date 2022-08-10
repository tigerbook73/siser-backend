<?php

namespace Tests\Feature;

class SoftwarePackageUpdateApiTest extends SoftwarePackageTestCase
{

  public ?string $role = 'admin';

  public function testSoftwarePackageUpdateOk()
  {
    $this->updateAssert(200, $this->object->id);
  }

  public function testSoftwarePackageUpdateSuccess()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * success name
     */
    unset($this->modelUpdate['name']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['name'] = 'Launch day';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['name'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    /**
     * success platform
     */
    unset($this->modelUpdate['platform']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = 'Mac';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['platform'] = 'Windows';
    $this->updateAssert(200, $this->object->id);

    /**
     * success version
     */
    unset($this->modelUpdate['version']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version'] = '1.0.1';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['version'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    /**
     * success version type
     */
    unset($this->modelUpdate['version_type']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = 'stable';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['version_type'] = 'beta';
    $this->updateAssert(200, $this->object->id);

    /**
     * success released date
     */
    unset($this->modelUpdate['released_date']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-08-04 00:00:00';
    $this->updateAssert(200, $this->object->id);

    /**
     * success filename
     */
    unset($this->modelUpdate['filename']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['filename'] = 'happy_driver_2022';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['filename'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    /**
     * success url
     */
    unset($this->modelUpdate['url']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['url'] = 'https://st-software.siser.com/software-packages/download?version=xkxalk12312ljj';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['url'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    /**
     * success description
     */
    unset($this->modelUpdate['description']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['description'] = 'Lenarodo Design Studio 6.0.1 for Mac';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['description'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    /**
     * success release notes
     */
    unset($this->modelUpdate['release_notes']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes'] = 'https://st-software.siser.com/software-packages/lds/6.0.1/release_notes';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['release_notes'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);
  }

  public function testSoftwarePackageUpdateReleasedDateSuccess()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * success released date
     */
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '31-12-2022 00:00:00';
    $response = $this->updateAssert(200, $this->object->id);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 00:00:00');

    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '31-12-2022 15:43:31';
    $response = $this->updateAssert(200, $this->object->id);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 15:43:31');

    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '31-12-2022';
    $response = $this->updateAssert(200, $this->object->id);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 00:00:00');

    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-12-31';
    $response = $this->updateAssert(200, $this->object->id);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31 00:00:00');
  }

  public function testSoftwarePackageUpdateError()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * error name
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['name'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['name'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error platform
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = 'abc';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error version
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error version type
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = 'xxx';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error released date
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = 'xxx';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-08-04 29:26:03';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-13-31 29:26:03';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-11-40 16:26:03';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error filename
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['filename'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['filename'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error url
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['url'] = '';
    $this->updateAssert(422, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['url'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error description
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['description'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);

    /**
     * error release notes
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes'] = $this->createRandomString(256);
    $this->updateAssert(422, $this->object->id);
  }

  public function testSoftwarePackageUpdateReleasedDateError()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * error released date
     */
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-12-33';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);

    $modelUpdate = $this->modelUpdate;
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-13-25';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);

    $modelUpdate = $this->modelUpdate;
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-13-25 14:09:30';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);

    $modelUpdate = $this->modelUpdate;
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-12-25 14:78:29';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);
  }
}
