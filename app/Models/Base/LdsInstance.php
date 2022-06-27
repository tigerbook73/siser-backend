<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LdsInstance
 * 
 * @property int $id
 * @property string $registration_code
 * @property int $user_id
 * @property string $device_id
 * @property Carbon $registration
 * @property Carbon|null $last_checkin
 * @property Carbon|null $expires_at
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class LdsInstance extends Model
{
  protected $table = 'lds_instances';

  protected $casts = [
    'user_id' => 'int'
  ];

  protected $dates = [
    'registration',
    'last_checkin',
    'expires_at'
  ];

  protected $fillable = [
    'registration_code',
    'user_id',
    'device_id',
    'registration',
    'last_checkin',
    'expires_at',
    'status'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
