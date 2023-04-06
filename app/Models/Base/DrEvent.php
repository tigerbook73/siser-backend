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
 * @property string $id
 * @property string $type
 * @property int $subscription_id
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
  public $incrementing = false;

  protected $casts = [
    'subscription_id' => 'int'
  ];

  protected $fillable = [
    'type',
    'subscription_id'
  ];
}
