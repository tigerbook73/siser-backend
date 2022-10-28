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
 * Class LdsRegistration
 * 
 * @property int $id
 * @property int $user_id
 * @property string $device_id
 * @property string $user_code
 * @property string $device_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $status
 * 
 * @property User $user
 *
 * @package App\Models\Base
 */
class LdsRegistration extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'lds_registrations';

  protected $casts = [
    'user_id' => 'int'
  ];

  protected $fillable = [
    'user_id',
    'device_id',
    'user_code',
    'device_name',
    'status'
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
