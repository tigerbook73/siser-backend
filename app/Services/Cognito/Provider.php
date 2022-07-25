<?php

namespace App\Services\Cognito;

use Exception;
use GuzzleHttp\Client;

use Aws\CognitoIdentityProvider\CognitoIdentityProviderClient;
use Aws\Exception\AwsException;


class CognitoUser
{
  public function __construct(
    public string $id,
    public string $username,
    public string $given_name,
    public string $family_name,
    public string $full_name,
    public string $email,
    public ?string $phone_number = null,
    public ?string $language_code = null,
    public ?string $country_code = null,
    public ?int $subscription_level = null,
  ) {
  }
}

class Provider
{
  public Client|null $httpClient = null;
  public CognitoIdentityProviderClient|null $cognitoClient = null;

  public string $region;
  public string $host;
  public string $userPoolId;
  public string $clientId;
  public string $clientSecret;
  public ?string $accessToken = null;

  public function __construct(string $accessToken = null)
  {
    $this->region = config('siser.aws_region');
    $this->host = config('siser.cognito.host');
    $this->userPoolId = config('siser.cognito.user_pool_id');
    $this->accessToken = $accessToken;
    $this->clientId = config('siser.cognito.client_id');
    $this->clientSecret = config('siser.cognito.client_secret');
  }

  public function setAccessToken(string $accessToken): void
  {
    $this->accessToken = $accessToken;
  }

  protected function getCognitoClient(): CognitoIdentityProviderClient
  {
    if (is_null($this->cognitoClient)) {
      $this->cognitoClient = new CognitoIdentityProviderClient([
        'profile' => 'default',
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
      subscription_level: $user['custom:subscription_level'] ?? null,
    );
  }

  /**
   * 
   */
  public function getCognitoUser(string $accessToken = null): ?CognitoUser
  {
    try {
      $result = $this->getCognitoClient()->getUser([
        'AccessToken' => $accessToken ?? $this->accessToken,
      ]);

      return $this->getCognitoUserFromApiResult($result);
    } catch (AwsException $e) {
      // output error message if fails
      echo $e->getMessage() . "\n";
      error_log($e->getMessage());
      return null;
    }
  }

  public function updateUserLicenseLevel(string $username, int $licenseLevel): void
  {
    try {
      $result = $this->getCognitoClient()->adminUpdateUserAttributes([
        'UserPoolId' => $this->userPoolId,
        'Username' => $username,
        'UserAttributes' => [
          ['Name' => 'custom:subscription_level', 'Value' => $licenseLevel]
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
