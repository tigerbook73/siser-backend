<?php

namespace App\Models;

use App\Models\Base\Coupon as BaseCoupon;

class Coupon extends BaseCoupon
{
  static protected $attributesOption = [
    'id'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'code'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'description'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'condition'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'percentage_off'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'period'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'start_date'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'end_date'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'status'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];


  public function scopePublic($query)
  {
    return $query->where('status', 'active');
  }

  protected function beforeCreate()
  {
    // update status
    if ($this->start_date > today()) {
      $this->status = 'draft';
    } else {
      $this->status = 'active';
    }
  }

  protected function beforeUpdate()
  {
    if ($this->status !== 'draft' && $this->status !== 'active') {
      return;
    }

    // update status
    if ($this->status == 'draft' && $this->start_date <= today()) {
      $this->status = 'active';
    }

    if ($this->status == 'active' && $this->end_date < today()) {
      $this->status = 'inactive';
    }
  }

  public function info()
  {
    return [
      'id'              => $this->id,
      'code'            => $this->code,
      'description'     => $this->description,
      'condition'       => $this->condition,
      'percentage_off'  => $this->percentage_off,
      'period'          => $this->period,
    ];
  }

  public function validate(bool $new_customer, bool $new_subscription = true, bool $upgrade_subscription = false): bool
  {
    return !(
      ($this->condition['new_customer_only'] && !$new_customer) ||
      ($this->condition['new_subscription_only'] && !$new_subscription) ||
      ($this->condition['upgrade_only'] && !$upgrade_subscription)
    );
  }
}
