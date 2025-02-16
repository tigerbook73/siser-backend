<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\CouponEvent;
use App\Models\Product;
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
 * @property string $name
 * @property string $product_name
 * @property string $type
 * @property string $coupon_event
 * @property string $discount_type
 * @property float $percentage_off
 * @property string $interval
 * @property int $interval_size
 * @property int $interval_count
 * @property array $condition
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $status
 * @property array|null $usage
 * @property array|null $meta
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Product $product
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
    'interval_size' => 'int',
    'interval_count' => 'int',
    'condition' => 'json',
    'start_date' => 'datetime',
    'end_date' => 'datetime',
    'usage' => 'json',
    'meta' => 'json'
  ];

  protected $fillable = [
    'code',
    'name',
    'product_name',
    'type',
    'coupon_event',
    'discount_type',
    'percentage_off',
    'interval',
    'interval_size',
    'interval_count',
    'condition',
    'start_date',
    'end_date',
    'status',
    'usage',
    'meta'
  ];

  public function coupon_event()
  {
    return $this->belongsTo(CouponEvent::class, 'coupon_event', 'name');
  }

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_name', 'name');
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }
}
