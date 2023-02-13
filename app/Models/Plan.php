<?php

namespace App\Models;

use App\Models\Base\Plan as BasePlan;

/**
 * Class Plan
 * 
 * @property array $price
 * 
 */
class Plan extends BasePlan
{
  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'catagory'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'description'         => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'subscription_level'  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'url'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'price'               => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_0_1],
    'price_list'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];


  public function scopePublic($query)
  {
    return $query->where('subscription_level', '>', 1)
      ->where('status', 'active');
  }

  public function getCustomerPlan(string $country)
  {
    $priceInCountry = null;
    foreach ($this->price_list as $price) {
      if ($price['country'] === $country) {
        $priceInCountry = $price;
      }
    }

    if (!$priceInCountry) {
      return null;
    }

    // return customer plan
    return [
      'id'                 => $this->id,
      'name'               => $this->name,
      'catagory'           => $this->catagory,
      'description'        => $this->description,
      'price'              => $priceInCountry,
      'subscription_level' => $this->subscription_level,
      'url'                => $this->url,
      'status'             => $this->status,
      'created_at'         => $this->created_at,
      'updated_at'         => $this->updated_at,
    ];
  }

  public function getSimplePlan(string $country)
  {
    $priceInCountry = null;
    foreach ($this->price_list as $price) {
      if ($price['country'] === $country) {
        $priceInCountry = $price;
      }
    }

    if (!$priceInCountry) {
      return null;
    }

    // return customer plan
    return [
      'name'      => $this->name,
      'country'   => $priceInCountry['country'],
      'currency'  => $priceInCountry['currency'],
      'price'     => $priceInCountry['price'],
    ];
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
