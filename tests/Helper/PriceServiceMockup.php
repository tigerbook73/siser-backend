<?php

namespace Tests\Helper;

use App\Models\LicensePackage;
use App\Models\Plan;
use App\Services\Paddle\PriceService;
use App\Services\Paddle\PaddleOperation;
use Paddle\SDK\Entities\Price;
use Paddle\SDK\Entities\Shared\Status;

class PriceServiceMockup extends PriceService
{
  /**
   * @param Plan $plan
   * @param PaddleOperation $mode
   * @param ?LicensePackage $licensePackage
   * @param int|null $quantity
   */
  public function fake(Plan $plan, PaddleOperation $mode, LicensePackage $licensePackage = null, int $quantity = null): Price
  {
    // prepare product
    $product = $plan->product;
    $product->setMetaPaddleProductId("pro_{$product->id}", $plan->getProductInterval())->save();

    $price = json_decode(PaddleTestHelper::serialize($this->prepareData($plan, $mode, $licensePackage, $quantity)), true);
    return Price::from(
      [
        ...$price,
        'id' => "pri_{$plan->id}",
        'product_id' => $product->getMeta()->paddle->getProductId($plan->getProductInterval()) ?? "pro_{$product->id}",
        'status' => $price['status'] ?? Status::Active()->getValue(),
        'created_at' => $plan->created_at,
        'updated_at' => $plan->updated_at,
      ]
    );
  }

  public function createPaddlePrice(Plan $plan): Price
  {
    $paddlePrice = $this->fake($plan, PaddleOperation::CREATE);
    $this->updatePlan($plan, $paddlePrice);
    return $paddlePrice;
  }

  public function updatePaddlePrice(Plan $plan): Price
  {
    $updatePrice = $this->fake($plan, PaddleOperation::UPDATE);
    $this->updatePlan($plan, $updatePrice);
    return $updatePrice;
  }

  public function createPaddleLicensePrice(Plan $plan, LicensePackage $licensePackage, int $quantity): Price
  {
    $paddlePrice = $this->fake($plan, PaddleOperation::CREATE, $licensePackage, $quantity);
    $this->updatePlan($plan, $paddlePrice);
    return $paddlePrice;
  }

  public function updatePaddleLicensePrice(Plan $plan, LicensePackage $licensePackage, int $quantity): Price
  {
    $updatePrice = $this->fake($plan, PaddleOperation::UPDATE, $licensePackage, $quantity);
    $this->updatePlan($plan, $updatePrice);
    return $updatePrice;
  }

  public function archivePrices(array|string $pricesIds): void
  {
    return;
  }
}
