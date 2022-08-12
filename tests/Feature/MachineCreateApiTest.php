<?php

namespace Tests\Feature;

class MachineCreateApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineCreateOk()
  {
    $this->createAssert();
  }

  public function testMachineCreateSerialNoSuccess()
  {
    $this->modelCreate['serial_no'] = $this->createRandomString(255);
    $this->createAssert();
  }

  public function testMachineCreateUserIDSuccess()
  {
    $this->modelCreate['user_id'] = $this->object->user_id;
    $this->createAssert();
  }

  public function testMachineCreateModelSuccess()
  {
    $this->modelCreate['model'] = $this->createRandomString(255);
    $this->createAssert();
  }

  public function testMachineCreateNicknameSuccess()
  {
    $this->modelCreate['nickname'] = $this->createRandomString(255);
    $this->createAssert();
  }

  public function testMachineCreateNicknameNotRequiredSuccess()
  {
    unset($this->modelCreate['nickname']);
    $this->createAssert();
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
