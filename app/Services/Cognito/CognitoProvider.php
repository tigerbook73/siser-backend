<?php

namespace App\Services\Cognito;

use GuzzleHttp\Client;
use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;


class CognitoProvider
{
  public Client|null $httpClient = null;
  public CognitoIdentityProviderClient|null $cognitoClient = null;

  public string $region;
  public string $userPoolId;
  public string $keyId;
  public string $keySecret;

  public function __construct()
  {
    $this->region     = config('siser.cognito.region');
    $this->userPoolId = config('siser.cognito.user_pool_id');
    $this->keyId      = config('siser.cognito.key_id');
    $this->keySecret  = config('siser.cognito.key_secret');
  }

  protected function getCognitoClient(): CognitoIdentityProviderClient
  {
    if (is_null($this->cognitoClient)) {
      $this->cognitoClient = new CognitoIdentityProviderClient([
        'profile' => $this->keyId ? null : 'siser',
        'credentials' => $this->keyId ? ['key' => $this->keyId, 'secret' => $this->keySecret] : null,
        'region'  => $this->region,
        'version' => '2016-04-18'
      ]);
    }

    return $this->cognitoClient;
  }

  /**
   * 
   */
  protected function getCognitoUserFromApiResult($result)
  {
    $username = $result['Username'];
    $user = [];
    foreach ($result['UserAttributes'] as $attribute) {
      $user[$attribute['Name']]  = $attribute['Value'];
    };

    return new CognitoUser(
      id: $user['sub'],
      username: $username,
      given_name: $user['given_name'] ?? '',
      family_name: $user['family_name'] ?? '',
      full_name: trim(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')),
      email: $user['email'],
      phone_number: $user['phone_number'] ?? null,
      language_code: $user['custom:language_code'] ?? null,
      country_code: $user['custom:country_code'] ?? null,
      is_lds_prem_sub: $user['custom:is_lds_prem_sub'] ?? null,
    );
  }

  /**
   * 
   */
  public function getCognitoUser(string $accessToken): ?CognitoUser
  {
    try {
      $result = $this->getCognitoClient()->getUser([
        'AccessToken' => $accessToken,
      ]);

      return $this->getCognitoUserFromApiResult($result);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
      return null;
    }
  }

  public function updateUserSubscriptionLevel(string $username, int $subscription_level): void
  {
    try {
      $result = $this->getCognitoClient()->adminUpdateUserAttributes([
        'UserPoolId' => $this->userPoolId,
        'Username' => $username,
        'UserAttributes' => [
          ['Name' => 'custom:is_lds_prem_sub', 'Value' => (string)($subscription_level >= 2 ? 1 : 0)]
        ],
      ]);
      // var_dump($result);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
    }
  }

  public function getUserByName(string $name): ?CognitoUser
  {
    try {
      $result = $this->getCognitoClient()->adminGetUser([
        'UserPoolId' => $this->userPoolId,
        'Username' => $name
      ]);

      return $this->getCognitoUserFromApiResult($result);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
      return null;
    }
  }
}
