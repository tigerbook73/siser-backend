<?php

namespace App\Services\Cognito;

class CognitoUser
{
  public function __construct(
    public string $id,
    public string $username,
    public string $given_name,
    public string $family_name,
    public string $full_name,
    public string $email,
    public bool $email_verified,
    public ?string $phone_number = null,
    public ?string $language_code = null,
    public ?string $country_code = null,
    public ?string $timezone = null,
    public ?int $is_lds_prem_sub = null,
    public ?int $software_user_id = null,
  ) {}
}
