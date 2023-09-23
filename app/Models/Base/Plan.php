<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models\Base;

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
 * 
 * @property Product $product
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
    'price_list' => 'json'
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
    'price_list'
  ];

  public function product()
  {
    return $this->belongsTo(Product::class, 'product_name', 'name');
  }

  public function subscriptions()
  {
    return $this->hasMany(Subscription::class);
  }
}
