<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GeneralConfiguration;

class MachineCreateApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineCreateOk()
  {
    $this->createAssert();
  }

  public function testMachineCreateUpdateOk()
  {
    $this->createAssert();

    $this->modelCreate['nickname'] = $this->modelCreate['nickname'] . '_update';
    $this->createAssert(200);
  }

  private function getUserCurrentLicenseCount()
  {
    $user = User::find($this->modelCreate['user_id']);
    return $user->seat_count;
  }

  public function testMachineCreateSerialNoSuccess()
  {
    $licenseCountBefore = $this->getUserCurrentLicenseCount();
    $this->modelCreate['serial_no'] = $this->createRandomString(255);
    $this->createAssert();
    $licenseCountAfter = $this->getUserCurrentLicenseCount();
    $this->assertEquals($licenseCountBefore + GeneralConfiguration::getMachineLicenseUnit(), $licenseCountAfter);
  }

  public function testMachineCreateUserIDSuccess()
  {
    $licenseCountBefore = $this->getUserCurrentLicenseCount();
    $this->modelCreate['user_id'] = $this->object->user_id;
    $this->createAssert();
    $licenseCountAfter = $this->getUserCurrentLicenseCount();
    $this->assertEquals($licenseCountBefore + GeneralConfiguration::getMachineLicenseUnit(), $licenseCountAfter);
  }

  public function testMachineCreateModelSuccess()
  {
    $licenseCountBefore = $this->getUserCurrentLicenseCount();
    $this->modelCreate['model'] = $this->createRandomString(255);
    $this->createAssert();
    $licenseCountAfter = $this->getUserCurrentLicenseCount();
    $this->assertEquals($licenseCountBefore + GeneralConfiguration::getMachineLicenseUnit(), $licenseCountAfter);
  }

  public function testMachineCreateNicknameSuccess()
  {
    $licenseCountBefore = $this->getUserCurrentLicenseCount();
    $this->modelCreate['nickname'] = $this->createRandomString(255);
    $this->createAssert();
    $licenseCountAfter = $this->getUserCurrentLicenseCount();
    $this->assertEquals($licenseCountBefore + GeneralConfiguration::getMachineLicenseUnit(), $licenseCountAfter);
  }

  public function testMachineCreateNicknameNotRequiredSuccess()
  {
    $licenseCountBefore = $this->getUserCurrentLicenseCount();
    unset($this->modelCreate['nickname']);
    $this->createAssert();
    $licenseCountAfter = $this->getUserCurrentLicenseCount();
    $this->assertEquals($licenseCountBefore + GeneralConfiguration::getMachineLicenseUnit(), $licenseCountAfter);
  }

  public function testMachineCreateDuplicatedSerialNo()
  {
    // create user2
    $userCreateFrom = [
      "create_from" => "username",
      "username" => $this->getDefaultTestUserName(),
    ];
    $this->postJson('/api/v1/users', $userCreateFrom);
    $user2 = User::where('name', $this->getDefaultTestUserName())->first();

    // create machine
    $response = $this->createAssert();

    // create duplicated machine
    $this->modelCreate['user_id'] = $user2->id;
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.serial_no', ['Machine with the serial_no already exists.']);
  }

  public function testMachineCreateError()
  {
    $modelCreate = $this->modelCreate;

    /**
     * error serial number
     */
    $this->modelCreate['serial_no'] = $this->createRandomString(256);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.serial_no', ['The serial no must not be greater than 255 characters.']);

    unset($this->modelCreate['serial_no']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.serial_no', ['The serial no field is required.']);


    /**
     * error user ID
     */
    $this->modelCreate = $modelCreate;
    $this->modelCreate['user_id'] = 999999999;
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.user_id', ['The selected user id is invalid.']);

    unset($this->modelCreate['user_id']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.user_id', ['The user id field is required.']);

    /**
     * error model
     */
    $this->modelCreate = $modelCreate;
    $this->modelCreate['model'] = $this->createRandomString(256);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.model', ['The model must not be greater than 255 characters.']);

    unset($this->modelCreate['model']);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.model', ['The model field is required.']);

    /**
     * error nickname
     */
    $this->modelCreate = $modelCreate;
    $this->modelCreate['nickname'] = $this->createRandomString(256);
    $response = $this->createAssert(422);
    $response->assertJsonPath('errors.nickname', ['The nickname must not be greater than 255 characters.']);
  }
}
