<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GeneralConfiguration;
use App\Models\Machine;
use App\Models\LdsPool;
use Tests\Trait\CognitoProviderMockup;

class MachineTransferApiTest extends MachineTestCase
{
  use CognitoProviderMockup;

  public ?string $role = 'admin';

  private function getUserLicenseCount(int $userId)
  {
    return User::find($userId)->license_count;
  }

  private function getUserLdsPool(int $userId)
  {
    return LdsPool::where("user_id", $userId)->first();
  }

  public function testMachineTransferOk()
  {
    // create new user
    $user = $this->postJson('/api/v1/users', [
      "create_from" => "username",
      "username" => $this->getDefaultTestUserName(),
    ])->json();

    $newUserLdsPool = LdsPool::where("user_id", $user['id'])->first();

    $machine = Machine::first();

    // get original user license count before
    $originalUserLicenseCountBefore = $this->getUserLicenseCount($machine->user_id);

    // transer machine to new user
    $response = $this->postJson("/api/v1/machines/{$machine->id}/transfer", ['new_user_id' => $user['id']]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    // get number of machine license per unit
    $numberOfLicensePerUnit = GeneralConfiguration::getMachineLicenseUnit();

    // get original user's license count after
    $originalUserLicenseCountAfter = $this->getUserLicenseCount($machine->user_id);
    $originalUserLdsPool = $this->getUserLdsPool($machine->user_id);
    $this->assertEquals($originalUserLicenseCountAfter, $originalUserLicenseCountBefore - $numberOfLicensePerUnit);
    $this->assertEquals($originalUserLdsPool->license_count, $originalUserLicenseCountAfter);
    $this->assertEquals($originalUserLdsPool->license_free, $originalUserLicenseCountAfter);

    // get new user's license count after
    $newUserLicenseCountAfter = $this->getUserLicenseCount($user['id']);
    $newUserLdsPool = $this->getUserLdsPool($user['id']);
    $this->assertEquals($newUserLicenseCountAfter, $numberOfLicensePerUnit);
    $this->assertEquals($newUserLdsPool->license_count, $numberOfLicensePerUnit);
    $this->assertEquals($newUserLdsPool->license_free, $numberOfLicensePerUnit);

    // Assert machine's user is new user
    $machine = Machine::first();
    $this->assertEquals($machine->user_id, $user['id']);
  }

  public function testMachineTransferSameUserOk()
  {
    $machine = Machine::first();

    $numberOfLicensePerUnit = GeneralConfiguration::getMachineLicenseUnit();

    // get user license count before
    $userLicenseCountBefore = $this->getUserLicenseCount($machine->user_id);
    $userLdsPool = $this->getUserLdsPool($machine->user_id);
    $this->assertEquals($userLicenseCountBefore, $numberOfLicensePerUnit);
    $this->assertEquals($userLdsPool->license_count, $userLicenseCountBefore);
    $this->assertEquals($userLdsPool->license_free, $userLicenseCountBefore);

    // transer machine to new user
    $response = $this->postJson("/api/v1/machines/{$machine->id}/transfer", ['new_user_id' => $machine->user_id]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    // get original user's license count after
    $userLicenseCountAfter = $this->getUserLicenseCount($machine->user_id);
    $userLdsPool = $this->getUserLdsPool($machine->user_id);
    $this->assertEquals($userLicenseCountAfter, $userLicenseCountBefore);
    $this->assertEquals($userLdsPool->license_count, $userLicenseCountAfter);
    $this->assertEquals($userLdsPool->license_free, $userLicenseCountAfter);
  }

  public function testMachineTransferNonExistMachineFail()
  {
    $machine = Machine::first();

    $response = $this->postJson("/api/v1/machines/0/transfer", ['new_user_id' => $machine->user_id]);
    $response->assertStatus(404);
  }

  public function testMachineTransferNonExistUserFail()
  {
    $machine = Machine::first();

    $response = $this->postJson("/api/v1/machines/{$machine->id}/transfer", ['new_user_id' => 0]);
    $response->assertStatus(422)->assertJsonValidationErrors(['new_user_id' => 'The selected new user id is invalid.']);
  }
}
