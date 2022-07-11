<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LdsInstance;
use App\Models\LicensePool;
use App\Models\Machine;
use App\Models\Subscription;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class User
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $cognito_id
 * @property string|null $given_name
 * @property string|null $family_name
 * @property string $full_name
 * @property string|null $phone_number
 * @property string|null $country_code
 * @property string|null $language_code
 * @property int|null $subscription_level
 * @property array|null $roles
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|LdsInstance[] $lds_instances
 * @property Collection|LicensePool[] $license_pools
 * @property Collection|Machine[] $machines
 * @property Collection|Subscription[] $subscriptions
 *
 * @package App\Models\Base
 */
class User extends \Illuminate\Foundation\Auth\User
{
  use HasFactory;
  use TraitModel;
  protected $table = 'users';

  protected $casts = [
    'subscription_level' => 'int',
    'roles' => 'json'
  ];

  protected $dates = [
    'email_verified_at'
  ];

  protected $fillable = [
    'name',
    'email',
    'password',
    'cognito_id',
    'given_name',
    'family_name',
    'full_name',
    'phone_number',
    'country_code',
    'language_code',
    'subscription_level',
    'roles'
  ];

  public function lds_instances()
  {
    return $this->hasMany(LdsInstance::class);
  }

  public function license_pools()
  {
    return $this->hasMany(LicensePool::class);
  }

  public function machines()
  {
    return $this->hasMany(Machine::class);
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }
}
