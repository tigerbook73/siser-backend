<?php

namespace App\Models;

use App\Models\Base\Plan as BasePlan;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Plan
 * 
 * @property array $price
 * 
 */
class Plan extends BasePlan
{
  const TYPE_STANDARD             = 'standard';
  const TYPE_TEST                 = 'test';

  const INTERVAL_DAY              = 'day';
  const INTERVAL_MONTH            = 'month';
  const INTERVAL_YEAR             = 'year';
  const INTERVAL_LONGTERM         = 'long-term'; // only for machine plan

  const STATUS_DRAFT              = 'draft';
  const STATUS_ACTIVE             = 'active';
  const STATUS_INACTIVE           = 'inactive';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'product_name'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'interval'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'interval_count'      => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'description'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'subscription_level'  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'url'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'price'               => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_0_1],
    'price_list'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];


  public function scopePublic($query)
  {
    return $query->where('subscription_level', '>', 1)
      ->where('status', 'active');
  }

  public function info(string $country): array|null
  {
    if ($priceInCountry = $this->getPrice($country)) {
      return [
        'id'                 => $this->id,
        'name'               => $this->name,
        'product_name'       => $this->product_name,
        'description'        => $this->description,
        'interval'           => $this->interval,
        'interval_count'     => $this->interval_count,
        'price'              => $priceInCountry,
        'subscription_level' => $this->subscription_level,
        'url'                => $this->url,
      ];
    }
    return null;
  }

  /**
   * @return array|null [
   *    'country'   => string,
   *    'currency'  => string,
   *    'price'     => float
   * ]
   */
  public function getPrice(string $country): array|null
  {
    foreach ($this->price_list as $price) {
      if ($price['country'] === $country) {
        return $price;
      }
    }
    return null;
  }

  public function activate()
  {
    if ($this->status !== 'draft') {
      return;
    }

    $this->status = 'active';
    $this->save();
  }

  public function deactivate()
  {
    if ($this->status !== 'active') {
      return;
    }

    $this->status = 'inactive';
    $this->save();
  }
}
