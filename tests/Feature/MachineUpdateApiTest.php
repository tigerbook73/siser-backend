<?php

namespace Tests\Feature;

class MachineUpdateApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  public function testMachineUpdateOk()
  {
    $this->updateAssert(200, $this->object->id);
  }

  public function testMachineUpdateSuccess()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * success model
     */
    $this->modelUpdate['model'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    unset($this->modelUpdate['model']);
    $this->updateAssert(200, $this->object->id);

    /**
     * success nickname
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['nickname'] = '';
    $this->updateAssert(200, $this->object->id);

    $this->modelUpdate['nickname'] = $this->createRandomString(255);
    $this->updateAssert(200, $this->object->id);

    unset($this->modelUpdate['nickname']);
    $this->updateAssert(200, $this->object->id);
  }

  public function testMachineUpdateError()
  {
    $modelUpdate = $this->modelUpdate;

    /**
     * error serial no
     */
    $this->modelUpdate['serial_no'] = $this->createRandomString(255);
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['serial_no'] = $this->createRandomString(256);
    $this->updateAssert(400, $this->object->id);

    /**
     * error user ID
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['user_id'] = $this->object->user_id;
    $this->updateAssert(400, $this->object->id);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['user_id'] = 'x';
    $this->updateAssert(400, $this->object->id);

    /**
     * error model
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['model'] = '';
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonPath('errors.model', ['The model must be a string.']);

    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['model'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonPath('errors.model', ['The model must not be greater than 255 characters.']);

    /**
     * error nickname
     */
    $this->modelUpdate = $modelUpdate;
    $this->modelUpdate['nickname'] = $this->createRandomString(256);
    $response = $this->updateAssert(422, $this->object->id);
    $response->assertJsonPath('errors.nickname', ['The nickname must not be greater than 255 characters.']);
  }
}
