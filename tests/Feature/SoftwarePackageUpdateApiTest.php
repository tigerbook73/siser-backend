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

    /**
     * success release notes text
     */
    $this->modelUpdate['release_notes_text'] = "";
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['release_notes_text'] = ["lines" => null];
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['release_notes_text'] = ["lines" => ""];
    $this->updateAssert(200, $this->object->id);

    unset($this->modelUpdate['release_notes_text']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes_text'] = ["lines" => []];
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['release_notes_text'] = ["lines" => [$this->createRandomString(256), "", $this->createRandomString(1024)]];
    $this->updateAssert(200, $this->object->id);

    /**
     * success file hash
     */
    unset($this->modelUpdate['file_hash']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['file_hash'] = '';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['file_hash'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    /**
     * success force update
     */
    unset($this->modelUpdate['force_update']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['force_update'] = 1;
    $this->updateAssert(200, $this->object->id);

    /**
     * success status
     */
    unset($this->modelUpdate['status']);
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['status'] = 'active';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['status'] = 'inactive';
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
      ->assertSeeText('2022-12-31');

    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '31-12-2022';
    $response = $this->updateAssert(200, $this->object->id);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31');

    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-12-31';
    $response = $this->updateAssert(200, $this->object->id);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema)
      ->assertSeeText('2022-12-31');
  }

  public function testSoftwarePackageUpdateError()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * error name
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['name'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['name' => 'The name field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['name'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['name' => 'The name must not be greater than 255 characters.']);

    /**
     * error platform
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = 'abc';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['platform' => 'he platform field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['platform'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['platform' => 'The selected platform is invalid.']);

    /**
     * error version
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['version' => 'The version field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['version' => 'The version must not be greater than 255 characters.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version'] = $this->createRandomString(255);
    $this->updateAssert(422, $this->object->id)->assertJsonValidationErrors(['version' => 'The version format is invalid.']);

    /**
     * error version type
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = 'xxx';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['version_type' => 'The selected version type is invalid.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['version_type' => 'The version type field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['version_type'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['version_type' => 'The selected version type is invalid.']);

    /**
     * error released date
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = 'xxx';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['released_date' => 'The released date field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-08-04 29:26:03';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-13-31 29:26:03';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-11-40 16:26:03';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    /**
     * error filename
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['filename'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['filename' => 'The filename field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['filename'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['filename' => 'The filename must not be greater than 255 characters.']);

    /**
     * error url
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['url'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['url' => 'The url field must have a value.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['url'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['url' => 'The url must not be greater than 255 characters.']);

    /**
     * error description
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['description'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['description' => 'The description must not be greater than 255 characters.']);

    /**
     * error release notes
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['release_notes' => 'The release notes must not be greater than 255 characters.']);

    /**
     * error release notes text
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes_text'] = $this->createRandomString(64);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['release_notes_text' => 'The release notes text must be an array.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes_text'] = ["line" => ["feature1 ...", "feature2 ..."]];
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['release_notes_text' => 'The release notes text must be an array.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['release_notes_text'] = ["lines" => [$this->createRandomString(64), null, $this->createRandomString(128)]];
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['release_notes_text.lines.1' => 'The release_notes_text.lines.1 must be a string.']);

    /**
     * error file hash
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['file_hash'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['file_hash' => 'The file hash must not be greater than 255 characters.']);

    /**
     * error force update
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['force_update'] = 'a';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['force_update' => 'The force update field must be true or false.']);

    /**
     * error status
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['status'] = 'true';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['status' => 'The selected status is invalid.']);

    $this->modelUpdate['status'] = 'all';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['status' => 'The selected status is invalid.']);

    $this->modelUpdate['status'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonValidationErrors(['status' => 'The status field must have a value.']);
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
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $modelUpdate = $this->modelUpdate;
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-13-25';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $modelUpdate = $this->modelUpdate;
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-13-25 14:09:30';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);

    $modelUpdate = $this->modelUpdate;
    $this->noAssert = TRUE;
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['released_date'] = '2022-12-25 14:78:29';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['released_date' => 'The released date is not a valid date.']);
  }
}
