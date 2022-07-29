<?php

namespace Tests\Trait;

use App\Models\User;
use App\Services\Cognito\CognitoProvider as CognitoProviderBase;
use App\Services\Cognito\CognitoUser;
use Exception;

class CognitoProvider extends CognitoProviderBase
{
  public string $testUserName = "user2.test";

  public function getCognitoUser(string $accessToken = null): ?CognitoUser
  {
    return new CognitoUser(
      id: 99999,
      username: $this->testUserName,
      given_name: "given_name",
      family_name: "family_name",
      full_name: "full_name",
      email: "email@email.com",
      phone_number: "+999999999",
      language_code: "en",
      country_code: "AU",
      subscription_level: 0,
    );
  }

  public function getUserByName(string $name): ?CognitoUser
  {
    /** @var User $user */
    $user = User::where('name', $name)->first();
    if ($user) {
      return new CognitoUser(
        id: $user->cognito_id,
        username: $user->name,
        given_name: $user->given_name,
        family_name: $user->family_name,
        full_name: $user->full_name,
        email: $user->email,
        phone_number: $user->phone_number,
        language_code: $user->language_code,
        country_code: $user->country_code,
        subscription_level: $user->subscription_level,
      );
    } else if ($name == $this->testUserName) {
      return $this->getCognitoUser();
    }
    return null;
  }
}

trait CognitoProviderMockup
{
  public function setupCognitoProviderMockup()
  {
    app()->bind(CognitoProviderBase::class, CognitoProvider::class);
  }

  public function getDefaultTestUserName()
  {
    return (new CognitoProvider())->testUserName;
  }
}
