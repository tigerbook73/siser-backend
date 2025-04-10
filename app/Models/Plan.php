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
  use TraitMetaAttr;

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
    'meta'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function scopePublic($query)
  {
    return $query->where('subscription_level', '>', 1)
      ->where('status', 'active');
  }

  public function info(string $country): ?PlanInfo
  {
    // special consideration for machine plan
    if ($this->id == config('siser.plan.default_machine_plan')) {
      $country = Country::findByCode($country) ?? Country::findByCode('US');
      return new PlanInfo(
        id: $this->id,
        name: $this->name,
        product_name: $this->product_name,
        description: $this->description,
        interval: $this->interval,
        interval_count: $this->interval_count,
        price: new PlanPrice($country->code, $country->currency, 0),
        subscription_level: $this->subscription_level,
        url: $this->url
      );
    }

    if ($priceInCountry = $this->getPrice($country)) {
      return new PlanInfo(
        id: $this->id,
        name: $this->name,
        product_name: $this->product_name,
        description: $this->description,
        interval: $this->interval,
        interval_count: $this->interval_count,
        price: PlanPrice::from($priceInCountry),
        subscription_level: $this->subscription_level,
        url: $this->url
      );
    }

    return null;
  }

  /**
   * @return ?array [
   *    'country'   => string,
   *    'currency'  => string,
   *    'price'     => float
   * ]
   */
  public function getPrice(string $country): ?array
  {
    foreach ($this->price_list as $price) {
      if ($price['country'] === $country) {
        return $price;
      }
    }
    return null;
  }

  public function getProductInterval(): ProductInterval
  {
    return ProductInterval::build($this->interval, $this->interval_count);
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

  /**
   * build plan name for display on UI
   */
  public function buildPlanName(int $licenseQuantity = 1): string
  {
    return $licenseQuantity <= 1
      ? $this->name
      : "{$this->name} (License x {$licenseQuantity})";
  }

  public function getMeta(): PlanMeta
  {
    return PlanMeta::from($this->meta ?? []);
  }

  public function setMeta(PlanMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddleProductId(?string $paddleProductId): self
  {
    $meta = $this->getMeta();
    if ($meta->paddle->product_id != $paddleProductId) {
      $meta->paddle->product_id = $paddleProductId;
      $this->setMeta($meta);
    }
    return $this;
  }

  public function setMetaPaddlePriceId(?string $paddlePriceId): self
  {
    $meta = $this->getMeta();
    if ($meta->paddle->price_id != $paddlePriceId) {
      $meta->paddle->price_id = $paddlePriceId;
      $this->setMeta($meta);
    }
    return $this->setMeta($meta);
  }

  public function setMetaPaddleLicensePrices(PlanMetaPaddleLicensePrices $licensePrices): self
  {
    $meta = $this->getMeta();
    $meta->paddle->license_prices = $licensePrices;
    return $this->setMeta($meta);
  }

  public function getMetaPaddleLicensePrices(): PlanMetaPaddleLicensePrices
  {
    return $this->getMeta()->paddle->license_prices;
  }

  public function getMetaPaddleLicensePriceId(int $quantity): ?string
  {
    return $this->getMetaPaddleLicensePrices()->getPriceId($quantity);
  }

  public function getMetaPaddleLicensePackageId(): ?int
  {
    return $this->getMetaPaddleLicensePrices()->license_package_id;
  }

  public function setMetaPaddleLicensePackageId(int $licensePackageId)
  {
    if ($this->getMetaPaddleLicensePrices()->license_package_id != $licensePackageId) {
      $this->setMetaPaddleLicensePrices(
        $this->getMetaPaddleLicensePrices()->setLicensePackageId($licensePackageId)
      );
    }
    return $this;
  }

  public function setMetaPaddleLicensePriceId(int $quantity, string $priceId): self
  {
    if ($this->getMetaPaddleLicensePrices()->getPriceId($quantity) != $priceId) {
      $this->setMetaPaddleLicensePrices(
        $this->getMetaPaddleLicensePrices()->setPriceId($quantity, $priceId)
      );
    }
    return $this;
  }

  /**
   * @return string[]
   */
  public function getAllPriceIds(): array
  {
    $planMeta = $this->getMeta();
    return array_filter(array_merge(
      [$planMeta->paddle->price_id],
      $planMeta->paddle->license_prices->getPriceIds()
    ));
  }
}
