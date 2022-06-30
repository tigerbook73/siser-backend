<?php

namespace App\Models;

use App\Models\Base\User as BaseUser;
use App\Services\Cognito\CognitoUser;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseUser
{
  use HasApiTokens, Notifiable;

  static protected $attributesOption = [
    'id'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'full_name'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'email'               => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'cognito_id'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'country'             => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'language'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'subscription_level'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
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
  }

  static public function createFromCognitoUser(CognitoUser $cognitoUser)
  {
    if (User::where('cognito_id', $cognitoUser->id)->count() > 0) {
      abort(400, 'user already exists');
    }

    $user = User::create([
      'name' => $cognitoUser->username,
      'cognito_id' => $cognitoUser->id,
      'email' => $cognitoUser->email,
      'full_name' => $cognitoUser->name,
      'country' => $cognitoUser->country_code,
      'language' => $cognitoUser->language_code,
      'password' => 'not allowed',
    ]);

    return $user;
  }

  public function updateFromCognitoUser(CognitoUser $cognitoUser)
  {
    if ($this->name !== $cognitoUser->username) {
      abort(500, 'Something wrong');
    }

    $this->full_name = $cognitoUser->name;
    $this->country = $cognitoUser->country_code;
    $this->language = $cognitoUser->language_code;
    $this->save();
  }
}
