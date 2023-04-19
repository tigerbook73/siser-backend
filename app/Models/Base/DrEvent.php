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
 * Class DrEvent
 * 
 * @property int $id
 * @property string $event_id
 * @property string $type
 * @property int|null $subscription_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class DrEvent extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'dr_events';

  protected $casts = [
    'subscription_id' => 'int'
  ];

  protected $fillable = [
    'event_id',
    'type',
    'subscription_id'
  ];
}
