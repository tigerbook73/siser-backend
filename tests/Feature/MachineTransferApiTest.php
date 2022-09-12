<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GeneralConfiguration;
use App\Models\Machine;
use Tests\Trait\CognitoProviderMockup;

class MachineTransferApiTest extends MachineTestCase
{
  use CognitoProviderMockup;

  public ?string $role = 'admin';

  public function testMachineTransferOk()
  {
    // TODO: test cases need to be designed

    // create new user
    $user = $this->postJson('/api/v1/users', [
      "create_from" => "username",
      "username" => $this->getDefaultTestUserName(),
    ])->json();

    $machine = Machine::first();

    // transer machine to new user
    $response = $this->postJson("/api/v1/machines/{$machine->id}/transfer", ['new_user_id' => $user['id']]);
    $response->assertStatus(200)
      ->assertJsonStructure($this->modelSchema);

    // TODO
    // assert original user (license_count)
    // assert new user 
    // assert lds pool
  }

  // TODO: more test cases to come
}
