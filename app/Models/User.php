<?php

namespace App\Models;

use App\Events\UserSaved;
use App\Events\UserSubscriptionLevelChanged;
use App\Services\Cognito\CognitoUser;
use Illuminate\Notifications\Notifiable;

class User extends UserWithTrait
{
  use Notifiable;

  static protected $attributesOption = [
    'id'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'cognito_id'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'given_name'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'family_name'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'full_name'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'email'               => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'phone_number'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'country_code'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'language_code'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'  => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_count'       => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];


  public function newQuery()
  {
    return parent::newQuery()->whereNotNull('cognito_id');
  }

  protected function beforeCreate()
  {
    $this->subscription_level = 0;
    $this->license_count = 0;
    $this->roles = null;
  }

  protected function afterCreate()
  {
    UserSaved::dispatch($this);
  }

  protected function afterUpdate()
  {
    if ($this->wasChanged('subscription_level')) {
      UserSubscriptionLevelChanged::dispatch($this);
    }
    if ($this->wasChanged(['subscription_level', 'license_count'])) {
      UserSaved::dispatch($this);
    };
  }

  static public function createOrUpdateFromCognitoUser(CognitoUser $cognitoUser): User
  {
    /** @var User|null $user */
    $user = User::where('cognito_id', $cognitoUser->id)->first();
    if ($user) {
      $user->updateFromCognitoUser($cognitoUser);
    } else {
      $user = User::create([
        'name'          => $cognitoUser->username,
        'cognito_id'    => $cognitoUser->id,
        'email'         => $cognitoUser->email,
        'given_name'    => $cognitoUser->given_name,
        'family_name'   => $cognitoUser->family_name,
        'full_name'     => $cognitoUser->full_name,
        'phone_number'  => $cognitoUser->phone_number,
        'country_code'  => $cognitoUser->country_code,
        'language_code' => $cognitoUser->language_code,
        'password'      => 'not allowed',
      ]);
    }
    return $user;
  }

  public function updateFromCognitoUser(CognitoUser $cognitoUser): User
  {
    if ($this->cognito_id !== $cognitoUser->id) {
      abort(500, 'Something wrong when updating from cognito user');
    }

    $this->name           = $cognitoUser->username;
    $this->email          = $cognitoUser->email;
    $this->given_name     = $cognitoUser->given_name;
    $this->family_name    = $cognitoUser->family_name;
    $this->full_name      = $cognitoUser->full_name;
    $this->phone_number   = $cognitoUser->phone_number;
    $this->country_code   = $cognitoUser->country_code;
    $this->language_code  = $cognitoUser->language_code;
    $this->save();

    return $this;
  }
}
