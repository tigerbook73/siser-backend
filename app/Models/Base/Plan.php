<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

use App\Models\LicensePlan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\TraitModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Plan
 * 
 * @property int $id
 * @property string $name
 * @property string $product_name
 * @property string $interval
 * @property int $interval_count
 * @property string $description
 * @property int $subscription_level
 * @property string|null $url
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array $price_list
 * @property array|null $meta
 * 
 * @property Product $product
 * @property LicensePlan $license_plan
 * @property Collection|Subscription[] $subscriptions
 *
 * @package App\Models\Base
 */
class Plan extends Model
{
  use HasFactory;
  use TraitModel;
  protected $table = 'plans';

  protected $casts = [
    'interval_count' => 'int',
    'subscription_level' => 'int',
    'price_list' => 'json',
    'meta' => 'json'
  ];

  protected $fillable = [
    'name',
    'product_name',
    'interval',
    'interval_count',
    'description',
    'subscription_level',
    'url',
    'status',
    'price_list',
    'meta'
  ];

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_name', 'name');
  }

  public function license_plan()
  {
    return $this->hasOne(LicensePlan::class);
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }
}
