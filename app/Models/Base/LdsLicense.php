<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LdsLicense
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_level
 * @property int $license_count
 * @property int $license_free
 * @property int $license_used
 * @property int $latest_expires_at
 * @property int $lastest_expires_at
 * @property array|null $devices
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class LdsLicense extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'lds_licenses';

  protected $casts = [
    'user_id' => 'int',
    'subscription_level' => 'int',
    'license_count' => 'int',
    'license_free' => 'int',
    'license_used' => 'int',
    'latest_expires_at' => 'int',
    'lastest_expires_at' => 'int',
    'devices' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'subscription_level',
    'license_count',
    'license_free',
    'license_used',
    'latest_expires_at',
    'lastest_expires_at',
    'devices'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
