<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LdsPool;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LdsInstance
 * 
 * @property int $id
 * @property int $lds_pool_id
 * @property int $lds_registration_id
 * @property int $user_id
 * @property string $device_id
 * @property string $user_code
 * @property Carbon $registered_at
 * @property bool $online
 * @property int $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $status
 * 
 * @property LdsPool $lds_pool
 *
 * @package App\Models\Base
 */
class LdsInstance extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'lds_instances';

  protected $casts = [
    'lds_pool_id' => 'int',
    'lds_registration_id' => 'int',
    'user_id' => 'int',
    'online' => 'bool',
    'expires_at' => 'int'
  ];

  protected $dates = [
    'registered_at'
  ];

  protected $fillable = [
    'lds_pool_id',
    'lds_registration_id',
    'user_id',
    'device_id',
    'user_code',
    'registered_at',
    'online',
    'expires_at',
    'status'
  ];

  public function lds_pool()
  {
    return $this->belongsTo(LdsPool::class);
  }
}
