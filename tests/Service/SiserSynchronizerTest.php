<?php

namespace Tests\Service;

use App\Models\User;
use App\Services\Cognito\CognitoProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiserSynchronizerTest extends TestCase
{
  use RefreshDatabase;

  protected $seed = true;

  public function testUserSubscriptionLevel()
  {
    $cognito = new CognitoProvider();

    /** @var User $user */
    $user = User::first();
    $original_level = $user->subscription_level;


    $user->subscription_level = 0;
    $user->save();
    $cognitoUser = $cognito->getUserByName($user->name);
    $this->assertEquals(0, $cognitoUser->is_lds_prem_sub);

    $user->subscription_level = 1;
    $user->save();
    $cognitoUser = $cognito->getUserByName($user->name);
    $this->assertEquals(0, $cognitoUser->is_lds_prem_sub);

    $user->subscription_level = 2;
    $user->save();
    $cognitoUser = $cognito->getUserByName($user->name);
    $this->assertEquals(1, $cognitoUser->is_lds_prem_sub);

    // change data back
    $user->subscription_level = $original_level;
    $user->save();
    $cognitoUser = $cognito->getUserByName($user->name);
    $this->assertEquals($original_level >= 2 ? 1 : 0, $cognitoUser->is_lds_prem_sub);
  }
}
