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
 * @property int|null $subscription_id
 * @property string $status
 * @property array|null $status_transitions
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
    'subscription_id' => 'int',
    'status_transitions' => 'json'
  ];

  protected $fillable = [
    'event_id',
    'type',
    'subscription_id',
    'status',
    'status_transitions'
  ];
}
