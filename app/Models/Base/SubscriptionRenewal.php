<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Subscription;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubscriptionRenewal
 * 
 * @property int $id
 * @property int $user_id
 * @property int $subscription_id
 * @property int $period
 * @property Carbon $start_at
 * @property Carbon $expire_at
 * @property Carbon|null $first_reminder_at
 * @property Carbon|null $final_reminder_at
 * @property string $status
 * @property string $sub_status
 * @property array|null $status_transitions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Subscription $subscription
 * @property User $user
 *
 * @package App\Models\Base
 */
class SubscriptionRenewal extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'subscription_renewals';

  protected $casts = [
    'user_id' => 'int',
    'subscription_id' => 'int',
    'period' => 'int',
    'start_at' => 'datetime',
    'expire_at' => 'datetime',
    'first_reminder_at' => 'datetime',
    'final_reminder_at' => 'datetime',
    'status_transitions' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'subscription_id',
    'period',
    'start_at',
    'expire_at',
    'first_reminder_at',
    'final_reminder_at',
    'status',
    'sub_status',
    'status_transitions'
  ];

  public function subscription()
  {
    return $this->belongsTo(Subscription::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
