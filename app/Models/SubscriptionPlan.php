<?php

namespace App\Models;

use App\Models\Base\SubscriptionPlan as BaseSubscriptionPlan;

class SubscriptionPlan extends BaseSubscriptionPlan
{
  const TYPE_STANDARD = 'standard';
  const TYPE_FREE_TRIAL = 'free-trial';
  const TYPE_TEST = 'test';

  const INTERVAL_MONTH = 'month';
  const INTERVAL_YEAR = 'year';
  const INTERVAL_DAY = 'day';
  const INTERVAL_WEEK = 'week';

  const STATUS_ACTIVE = 'active';
  const STATUS_INACTIVE = 'inactive';

  static protected $attributesOption = [
    'id'                      => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'name'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'type'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'interval'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'interval_count'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'contract_binding_days'   => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'billing_offset_days'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'reminder_offset_days'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'collection_period_days'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'status'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'created_at'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  /**
   * @param string $type
   */
  static public function buildPlanName(string $type, string $interval, int $interval_count, ?string $suffix = null)
  {
    return $type . '-' . $interval_count . '-' . $interval . ($suffix ? '-' . $suffix : '');
  }

  static public function findByTypeAndIterval(string|array $type, string $interval, int $interval_count, ?string $suffix = null): ?self
  {
    $types = (gettype($type) === 'string') ? [$type] : $type;
    $names = [];
    foreach ($types as $type) {
      $names[] = self::buildPlanName($type, $interval, $interval_count, $suffix);
    }
    return self::whereIn('name', $names)->where('status', self::STATUS_ACTIVE)->first();
  }

  public function beforeCreate()
  {
    $name = self::buildPlanName($this->type, $this->interval, $this->interval_count);
    $this->name = $this->name ?? $name;
  }
}
