<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LdsInstance;
use App\Models\LicensePool;
use App\Models\Machine;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

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
 * @property Collection|LdsInstance[] $lds_instances
 * @property Collection|LicensePool[] $license_pools
 * @property Collection|Machine[] $machines
 * @property Collection|Subscription[] $subscriptions
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
