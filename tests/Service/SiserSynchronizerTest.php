<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Cognito\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiserSynchronizerTest extends TestCase
{
  use RefreshDatabase;

  protected $seed = true;

  public function testUserSubscriptionLevel()
  {
    $cognito = new Provider;


    /** @var User $user */
    $user = User::first();
    $user->subscription_level = 9;
    $user->save();

    $cognitoUser = $cognito->getUserByName($user->name);
    $this->assertEquals(9, $cognitoUser->subscription_level);
  }
}
