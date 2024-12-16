<?php

namespace App\Models;

use App\Models\Base\LicensePlan as BaseLicensePlan;
use Illuminate\Support\Collection;

class LicensePlan extends BaseLicensePlan
{
  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'product_name'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_package_id'  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'interval'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'interval_count'      => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'details'             => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];


  /**
   * Create a new LicensePlan from a LicensePackage and a Subscriptin Plan
   *
   * @param LicensePackage $licensePackage
   * @param Plan $plan
   */
  static public function createFrom(LicensePackage $licensePackage, Plan $plan)
  {
    if ($plan->subscription_level < 2) {
      throw new \Exception('Plan must be a subscription plan');
    }

    /** @var Product $licenseProduct */
    $licenseProduct = Product::where('type', Product::TYPE_LICENSE_PACKAGE)->first();

    $licensePlan = new LicensePlan();
    $licensePlan->product_name        = $licenseProduct->name;
    $licensePlan->license_package_id  = $licensePackage->id;
    $licensePlan->plan_id             = $plan->id;
    $licensePlan->interval            = $plan->interval;
    $licensePlan->interval_count      = $plan->interval_count;

    $details = [];

    $maxQuantity = $licensePackage->getMaxQuantity();
    for ($i = 0; $i < $maxQuantity; $i++) {
      $quantity = $i + 1;
      $details[] = LicensePlanDetail::from([
        'name'            => $licensePackage->name . ' x ' . $quantity,
        'quantity'        => $quantity,
        'price_rate'      => $licensePackage->info($quantity)['price_rate'],
        'paddle_price_id' => null,
      ])->toArray();
    }
    $licensePlan->details = $details;
    $licensePlan->save();
    return $licensePlan;
  }

  /**
   *
   */
  public function refreshDetails()
  {
    $licensePackage = $this->license_package;
    $plan = $this->plan;

    $details =  array_slice($this->details, 0, $licensePackage->getMaxQuantity());
    $maxQuantity = $licensePackage->getMaxQuantity();
    for ($i = 0; $i < $maxQuantity; $i++) {
      $quantity = $i + 1;
      // use merge to make the order of fields not changed
      $details[$i] = array_merge(
        $details[$i] ?? [],
        LicensePlanDetail::from([
          'name'            => $licensePackage->name . ' x ' . $quantity,
          'quantity'        => $quantity,
          'price_rate'      => $licensePackage->info($quantity)['price_rate'],
          'paddle_price_id' => $details[$i]['paddle_price_id'] ?? null,
        ])->toArray()
      );
    }
    $this->details = $details;
    $this->save();

    return $this->wasChanged();
  }

  public function getDetail(int $quantity): LicensePlanDetail
  {
    return LicensePlanDetail::from($this->details[$quantity - 1]);
  }

  public function setDetail(LicensePlanDetail $detail): self
  {
    if ($detail->quantity <= 0 || $detail->quantity > count($this->details)) {
      throw new \Exception('Invalid quantity for LicensePlanDetail');
    }

    $details = $this->details;
    $details[$detail->quantity - 1] = $detail->toArray();
    $this->details = $details;
    return $this;
  }

  static public function createOrRefreshAll()
  {
    /** @var Collection<int, LicensePackage> $licensePackages */
    $licensePackages = LicensePackage::where('type', LicensePackage::TYPE_STANDARD)->get();

    /** @var Collection<int, Plan> $plans */
    $plans = Plan::where('subscription_level', '>', 1)->get();

    foreach ($licensePackages as $licensePackage) {
      foreach ($plans as $plan) {
        /** @var ?LicensePlan $licensePlan */
        $licensePlan = LicensePlan::where('license_package_id', $licensePackage->id)
          ->where('plan_id', $plan->id)
          ->first();
        if ($licensePlan) {
          $licensePlan->refreshDetails();
        } else {
          LicensePlan::createFrom($licensePackage, $plan);
        }
      }
    }
  }
}
