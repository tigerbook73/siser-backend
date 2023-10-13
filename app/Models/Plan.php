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
    'next_plan_info'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
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

  public function buildNextPlanInfo()
  {
    return [
      'id'                  => $this->id,
      'name'                => $this->name,
      'product_name'        => $this->product_name,
      'description'         => $this->description,
      'interval'            => $this->interval,
      'interval_count'      => $this->interval_count,
    ];
  }

  static public function findNextMonthPlan(Plan $annualPlan): Plan|null
  {
    if ($annualPlan->interval !== Plan::INTERVAL_YEAR || $annualPlan->status !== Plan::STATUS_ACTIVE) {
      return null;
    }

    $monthPlan = Plan::public()
      ->where('interval', Plan::INTERVAL_MONTH)
      ->where('interval_count', 1)
      ->where('subscription_level', $annualPlan->subscription_level)
      ->where('product_name', $annualPlan->product_name)
      ->whereJsonContains('price_list', ['country' => $annualPlan->price_list[0]['country']])
      ->first();
    return $monthPlan;
  }

  static public function validPlanPair(Plan $annualPlan, Plan $monthPlan = null)
  {
    if (!$monthPlan) {
      throw new HttpException(400, 'month plan not found for annual plan ' . $annualPlan->id);
    }

    if ($monthPlan->status != Plan::STATUS_ACTIVE) {
      throw new HttpException(400, 'month plan not active for annual plan ' . $annualPlan->id);
    }

    $annualPlanPriceList = $annualPlan->price_list;
    foreach ($annualPlanPriceList as $annualPlanPrice) {
      $monthPlanPrice = $monthPlan->getPrice($annualPlanPrice['country']);
      if (!$monthPlanPrice) {
        throw new HttpException(400, 'month plan price not found for country ' . $annualPlanPrice['country']);
      }

      if ($monthPlanPrice['currency'] !== $annualPlanPrice['currency']) {
        throw new HttpException(400, 'currency not match for country ' . $annualPlanPrice['country']);
      }
    }
  }

  public function validatePlan()
  {
    /**
     * only validate active annual plan
     */
    if ($this->interval === Plan::INTERVAL_YEAR && $this->status == Plan::STATUS_ACTIVE) {
      self::validPlanPair($this, $this->next_plan);
    } else if ($this->interval === Plan::INTERVAL_MONTH) {
      // not new plan
      if ($this->id) {
        foreach (Plan::public()->where('next_plan_id', $this->id)->get() as $annualPlan) {
          self::validPlanPair($annualPlan, $this);
        }
      }
    }
  }

  protected function beforeSave()
  {
    if (!$this->next_plan_id) {
      $this->next_plan_id = Plan::findNextMonthPlan($this)?->id;
      if ($this->next_plan_id) {
        $this->next_plan_info = $this->next_plan->buildNextPlanInfo();
      }
    }

    $this->validatePlan();
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
