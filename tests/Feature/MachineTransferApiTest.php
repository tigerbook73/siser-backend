<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GeneralConfiguration;
use App\Models\LdsLicense;
use App\Models\Machine;

class MachineTransferApiTest extends MachineTestCase
{
  public ?string $role = 'admin';

  private function getUserLicenseCount(int $userId)
  {
    return User::find($userId)->license_count;
  }

  private function getLdsLicense(int $userId)
  {
    return LdsLicense::fromUserId($userId);
  }

  public function testMachineTransferOk()
  {
    // create new user
    $user = $this->postJson('/api/v1/users', [
      "create_from" => "username",
      "username" => $this->getDefaultTestUserName(),
    ])->json();

    $newLdsLicense = LdsLicense::fromUserId($user['id']);

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
    $originalLdsLicense = $this->getLdsLicense($machine->user_id);
    $this->assertEquals($originalUserLicenseCountAfter, $originalUserLicenseCountBefore - $numberOfLicensePerUnit);
    $this->assertEquals($originalLdsLicense->license_count, $originalUserLicenseCountAfter);
    $this->assertEquals($originalLdsLicense->license_free, $originalUserLicenseCountAfter);

    // get new user's license count after
    $newUserLicenseCountAfter = $this->getUserLicenseCount($user['id']);
    $newLdsLicense = $this->getLdsLicense($user['id']);
    $this->assertEquals($newUserLicenseCountAfter, $numberOfLicensePerUnit);
    $this->assertEquals($newLdsLicense->license_count, $numberOfLicensePerUnit);
    $this->assertEquals($newLdsLicense->license_free, $numberOfLicensePerUnit);

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
    $ldsLicense = $this->getLdsLicense($machine->user_id);
    $this->assertEquals($userLicenseCountBefore, $numberOfLicensePerUnit);
    $this->assertEquals($ldsLicense->license_count, $userLicenseCountBefore);
    $this->assertEquals($ldsLicense->license_free, $userLicenseCountBefore);

    // transer machine to new user
    $response = $this->postJson("/api/v1/machines/{$machine->id}/transfer", ['new_user_id' => $machine->user_id]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    // get original user's license count after
    $userLicenseCountAfter = $this->getUserLicenseCount($machine->user_id);
    $ldsLicense = $this->getLdsLicense($machine->user_id);
    $this->assertEquals($userLicenseCountAfter, $userLicenseCountBefore);
    $this->assertEquals($ldsLicense->license_count, $userLicenseCountAfter);
    $this->assertEquals($ldsLicense->license_free, $userLicenseCountAfter);
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
