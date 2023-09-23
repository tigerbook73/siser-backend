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
 * Class SubscriptionPlan
 * 
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $interval
 * @property int $interval_count
 * @property int $contract_binding_days
 * @property int $billing_offset_days
 * @property int $reminder_offset_days
 * @property int $collection_period_days
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models\Base
 */
class SubscriptionPlan extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'subscription_plans';

  protected $casts = [
    'interval_count' => 'int',
    'contract_binding_days' => 'int',
    'billing_offset_days' => 'int',
    'reminder_offset_days' => 'int',
    'collection_period_days' => 'int'
  ];

  protected $fillable = [
    'name',
    'type',
    'interval',
    'interval_count',
    'contract_binding_days',
    'billing_offset_days',
    'reminder_offset_days',
    'collection_period_days',
    'status'
  ];
}
