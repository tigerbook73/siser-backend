<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\LicenseSharing;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\SubscriptionRenewal;
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
 * @property array|null $tax_id_info
 * @property array|null $plan_info
 * @property array|null $coupon_info
 * @property array|null $license_package_info
 * @property array|null $items
 * @property array|null $payment_method_info
 * @property float $subtotal
 * @property float $tax_rate
 * @property float $total_tax
 * @property float $total_amount
 * @property int $subscription_level
 * @property int $current_period
 * @property Carbon|null $current_period_start_date
 * @property Carbon|null $current_period_end_date
 * @property Carbon|null $next_invoice_date
 * @property Carbon|null $next_reminder_date
 * @property array|null $next_invoice
 * @property array|null $renewal_info
 * @property array|null $dr
 * @property string|null $dr_subscription_id
 * @property string|null $stop_reason
 * @property string $sub_status
 * @property int|null $active_invoice_id
 * @property array|null $status_transitions
 * 
 * @property Coupon|null $coupon
 * @property Plan $plan
 * @property User $user
 * @property Collection|Invoice[] $invoices
 * @property LicenseSharing $license_sharing
 * @property Collection|Refund[] $refunds
 * @property Collection|SubscriptionRenewal[] $subscription_renewals
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
    'start_date' => 'datetime',
    'end_date' => 'datetime',
    'coupon_id' => 'int',
    'billing_info' => 'json',
    'tax_id_info' => 'json',
    'plan_info' => 'json',
    'coupon_info' => 'json',
    'license_package_info' => 'json',
    'items' => 'json',
    'payment_method_info' => 'json',
    'subtotal' => 'float',
    'tax_rate' => 'float',
    'total_tax' => 'float',
    'total_amount' => 'float',
    'subscription_level' => 'int',
    'current_period' => 'int',
    'current_period_start_date' => 'datetime',
    'current_period_end_date' => 'datetime',
    'next_invoice_date' => 'datetime',
    'next_reminder_date' => 'datetime',
    'next_invoice' => 'json',
    'renewal_info' => 'json',
    'dr' => 'json',
    'active_invoice_id' => 'int',
    'status_transitions' => 'json'
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
    'tax_id_info',
    'plan_info',
    'coupon_info',
    'license_package_info',
    'items',
    'payment_method_info',
    'subtotal',
    'tax_rate',
    'total_tax',
    'total_amount',
    'subscription_level',
    'current_period',
    'current_period_start_date',
    'current_period_end_date',
    'next_invoice_date',
    'next_reminder_date',
    'next_invoice',
    'renewal_info',
    'dr',
    'dr_subscription_id',
    'stop_reason',
    'sub_status',
    'active_invoice_id',
    'status_transitions'
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

  public function license_sharing()
  {
    return $this->hasOne(LicenseSharing::class);
  }

  public function refunds()
  {
    return $this->hasMany(Refund::class);
  }

  public function subscription_renewals()
  {
    return $this->hasMany(SubscriptionRenewal::class);
  }
}
