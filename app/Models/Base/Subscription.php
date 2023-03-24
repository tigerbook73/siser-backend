<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\TraitModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Subscription
 * 
 * @property int $id
 * @property int $user_id
 * @property int $plan_id
 * @property string $currency
 * @property float $price
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $coupon_id
 * @property array|null $billing_info
 * @property array|null $plan_info
 * @property array|null $coupon_info
 * @property array|null $processing_fee_info
 * @property float $processing_fee
 * @property float $tax
 * @property int $subscription_level
 * @property int $current_period
 * @property Carbon|null $current_period_start_date
 * @property Carbon|null $current_period_end_date
 * @property Carbon|null $next_invoice_date
 * @property array|null $dr
 * @property string|null $stop_reason
 * @property string $sub_status
 * 
 * @property Coupon|null $coupon
 * @property Plan $plan
 * @property User $user
 * @property Collection|Invoice[] $invoices
 *
 * @package App\Models\Base
 */
class Subscription extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'subscriptions';

  protected $casts = [
    'user_id' => 'int',
    'plan_id' => 'int',
    'price' => 'float',
    'start_date' => 'date',
    'end_date' => 'date',
    'coupon_id' => 'int',
    'billing_info' => 'json',
    'plan_info' => 'json',
    'coupon_info' => 'json',
    'processing_fee_info' => 'json',
    'processing_fee' => 'float',
    'tax' => 'float',
    'subscription_level' => 'int',
    'current_period' => 'int',
    'current_period_start_date' => 'date',
    'current_period_end_date' => 'date',
    'next_invoice_date' => 'date',
    'dr' => 'json'
  ];

  protected $fillable = [
    'user_id',
    'plan_id',
    'currency',
    'price',
    'start_date',
    'end_date',
    'status',
    'coupon_id',
    'billing_info',
    'plan_info',
    'coupon_info',
    'processing_fee_info',
    'processing_fee',
    'tax',
    'subscription_level',
    'current_period',
    'current_period_start_date',
    'current_period_end_date',
    'next_invoice_date',
    'dr',
    'stop_reason',
    'sub_status'
  ];

  public function coupon()
  {
    return $this->belongsTo(Coupon::class);
  }

  public function plan()
  {
    return $this->belongsTo(Plan::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function invoices()
  {
    return $this->hasMany(Invoice::class);
  }
}
