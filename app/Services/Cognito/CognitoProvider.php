<?php

namespace App\Services\Cognito;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;


class CognitoProvider
{
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

  public function getCognitoClient(): CognitoIdentityProviderClient
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
  public function getCognitoUserFromApiResult($result)
  {
    $username = $result['Username'];
    $user = [];
    foreach ($result['UserAttributes'] ?? $result['Attributes'] as $attribute) {
      $user[$attribute['Name']]  = $attribute['Value'];
    };

    return new CognitoUser(
      id: $user['sub'],
      username: $username,
      given_name: $user['given_name'] ?? '',
      family_name: $user['family_name'] ?? '',
      full_name: trim(($user['given_name'] ?? '') . ' ' . ($user['family_name'] ?? '')),
      email: $user['email'],
      email_verified: ($user['email_verified'] ?? null) == 'true',
      phone_number: $user['phone_number'] ?? null,
      language_code: $user['custom:language_code'] ?? null,
      country_code: $user['custom:country_code'] ?? null,
      timezone: $user['custom:timezone'] ?? null,
      is_lds_prem_sub: $user['custom:is_lds_prem_sub'] ?? null,
      software_user_id: $user['custom:software_user_id'] ?? null,
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

  public function updateUserCustomAttribute(string $username, string $attrName, string $attrValue): void
  {
    try {
      $this->getCognitoClient()->adminUpdateUserAttributes([
        'UserPoolId' => $this->userPoolId,
        'Username' => $username,
        'UserAttributes' => [
          ['Name' => 'custom:' . $attrName, 'Value' => $attrValue]
        ],
      ]);
      // var_dump($result);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
    }
  }

  public function updateUserSubscriptionLevel(string $username, int $subscription_level): void
  {
    $this->updateUserCustomAttribute($username, 'is_lds_prem_sub', (string)($subscription_level >= 2 ? 1 : 0));
  }

  public function updateUserId(string $username, int $software_user_id): void
  {
    $this->updateUserCustomAttribute($username, 'software_user_id', (string)($software_user_id));
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

  /**
   * @return CognitoUser[]
   */
  public function getCognitoUserList(bool $softwareUser = false, int $limit = 40): array
  {
    try {
      $result = $this->getCognitoClient()->listUsers([
        'UserPoolId' => $this->userPoolId,
        'Limit' => min($limit, 100),
      ]);

      $cognitoUsers = [];
      foreach ($result['Users'] as $user) {
        $cognitoUser = $this->getCognitoUserFromApiResult($user);
        if (
          ($softwareUser && $cognitoUser->software_user_id) ||
          (!$softwareUser && !$cognitoUser->software_user_id)
        ) {
          $cognitoUsers[] = $cognitoUser;
        }
      }

      return $cognitoUsers;
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
      return [];
    }
  }

  /**
   * @return CognitoUser[]
   */
  public function listCognitoUserByEmail(string $email, $limit = 10): array
  {
    try {
      $params = [
        'UserPoolId' => $this->userPoolId,
        'Limit' => min($limit, 100),
      ];

      if (!empty($email)) {
        $params['Filter'] = "email = \"$email\"";
      }

      $result = $this->getCognitoClient()->listUsers($params);

      $cognitoUsers = [];
      foreach ($result['Users'] as $user) {
        $cognitoUser = $this->getCognitoUserFromApiResult($user);
        $cognitoUsers[] = $cognitoUser;
      }

      return $cognitoUsers;
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
      return [];
    }
  }
}
