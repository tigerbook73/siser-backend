<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Subscription;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coupon
 * 
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property float $percentage_off
 * @property int $period
 * @property array $condition
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Subscription[] $subscriptions
 *
 * @package App\Models\Base
 */
class Coupon extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'coupons';

  protected $casts = [
    'percentage_off' => 'float',
    'period' => 'int',
    'condition' => 'json',
    'start_date' => 'datetime',
    'end_date' => 'datetime'
  ];

  protected $fillable = [
    'code',
    'description',
    'percentage_off',
    'period',
    'condition',
    'start_date',
    'end_date',
    'status'
  ];

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }
}
