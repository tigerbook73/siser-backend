<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use Carbon\Carbon;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $cognito_id
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class User extends \Illuminate\Foundation\Auth\User
{
  protected $table = 'users';

  protected $dates = [
    'email_verified_at'
  ];

  protected $fillable = [
    'name',
    'email',
    'cognito_id',
    'password'
  ];
}
