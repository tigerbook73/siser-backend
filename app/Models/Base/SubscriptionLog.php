<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SubscriptionLog
 * 
 * @property int $id
 * @property int $user_id
 * @property string $event
 * @property Carbon $date
 * @property Carbon $date_time
 * @property array $data
 *
 * @package App\Models\Base
 */
class SubscriptionLog extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'subscription_logs';
  public $timestamps = false;

  protected $casts = [
    'user_id' => 'int',
    'date' => 'datetime',
    'date_time' => 'datetime',
    'data' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'event',
    'date',
    'date_time',
    'data'
  ];
}
