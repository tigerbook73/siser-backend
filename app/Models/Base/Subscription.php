<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscription
 * 
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Plan $plan
 * @property User $user
 *
 * @package App\Models\Base
 */
class Subscription extends Model
{
  protected $table = 'subscriptions';

  protected $casts = [
    'user_id' => 'int',
    'plan_id' => 'int'
  ];

  protected $dates = [
    'start_date',
    'end_date'
  ];

  protected $fillable = [
    'user_id',
    'plan_id',
    'start_date',
    'end_date',
    'status'
  ];

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
