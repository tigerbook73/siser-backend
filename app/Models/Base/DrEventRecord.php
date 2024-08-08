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
 * Class DrEventRecord
 * 
 * @property int $id
 * @property string $event_id
 * @property string $type
 * @property int|null $user_id
 * @property int|null $subscription_id
 * @property array|null $data
 * @property array|null $messages
 * @property string $status
 * @property array|null $status_transitions
 * @property string $resolve_status
 * @property string $resolve_comments
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class DrEventRecord extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'dr_event_records';

  protected $casts = [
    'user_id' => 'int',
    'subscription_id' => 'int',
    'data' => 'json',
    'messages' => 'json',
    'status_transitions' => 'json'
  ];

  protected $fillable = [
    'event_id',
    'type',
    'user_id',
    'subscription_id',
    'data',
    'messages',
    'status',
    'status_transitions',
    'resolve_status',
    'resolve_comments'
  ];
}
