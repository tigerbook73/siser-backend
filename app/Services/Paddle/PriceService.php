<?php

namespace App\Services\Paddle;

use App\Models\LicensePackage;
use App\Models\Paddle\PriceCustomData;
use App\Models\PaddleMap;
use App\Models\Plan;
use App\Services\CountryHelper;
use App\Services\CurrencyHelper;
use Paddle\SDK\Entities\Price;
use Paddle\SDK\Entities\Shared\CatalogType;
use Paddle\SDK\Entities\Shared\CountryCode;
use Paddle\SDK\Entities\Shared\CurrencyCode;
use Paddle\SDK\Entities\Shared\Interval;
use Paddle\SDK\Entities\Shared\Money;
use Paddle\SDK\Entities\Shared\PriceQuantity;
use Paddle\SDK\Entities\Shared\Status;
use Paddle\SDK\Entities\Shared\TaxMode;
use Paddle\SDK\Entities\Shared\TimePeriod;
use Paddle\SDK\Entities\Shared\UnitPriceOverride;
use Paddle\SDK\Notifications\Entities\Price as EntitiesPrice;
use Paddle\SDK\Resources\Prices\Operations\CreatePrice;
use Paddle\SDK\Resources\Prices\Operations\UpdatePrice;

class PriceService extends PaddleEntityService
{
  public function getProPriceForEuCountry($country, $period): int
  {
    $total = ($period == Plan::INTERVAL_MONTH) ? 899 : 9700;
    $taxRate = CountryHelper::getEUCountryTaxRate($country);

    if ($taxRate == null) {
      throw new \Exception("Tax rate not found for EU country: {$country}");
    }

    return (int)($total / (1 + $taxRate));
  }

  /**
   * prepare CreatePrice or UpdatePrice from plan
   *
   * @param Plan $plan
   * @param PaddleOperation $mode
   * @param LicensePackage|null $licensePackage
   * @param int|null $quantity
   */
  public function prepareData(Plan $plan, PaddleOperation $mode, ?LicensePackage $licensePackage = null, ?int $quantity = null): CreatePrice|UpdatePrice
  {
    $customData = PriceCustomData::from([
      "product_name"      => $plan->product_name,
      "plan_id"           => $plan->id,
      "plan_name"         => $plan->name,
      "plan_timestamp"    => $plan->updated_at->format('Y-m-d H:i:s'),

      "license_package_id"        => $licensePackage ? $licensePackage->id : null,
      "license_quantity"          => $quantity,
      "license_package_timestamp" => $licensePackage ?
        max($plan->updated_at, $licensePackage->updated_at)->format('Y-m-d H:i:s') :
        null,
    ])->toCustomData();

    $priceList = $plan->price_list;

    $priceRate = 1;
    if ($licensePackage && $quantity > 1) {
      $priceRate = $licensePackage->getPriceTable()->getPriceRate($quantity) / LicensePackage::RATE_FACTOR;
    }

    // get default price (US) (tax exclusive)
    $usPrice = $plan->getPrice('US');

    $unitPrice = new Money(
      currencyCode: CurrencyCode::from($usPrice['currency']),
      amount: (string)(int)($usPrice['price'] * $priceRate * CurrencyHelper::getDecimalFactor($usPrice['currency'])),
    );

    /**
     * @var UnitPriceOverride[] $unitPriceOverrides
     */
    $unitPriceOverrides = [];
    foreach ($priceList as $price) {
      // skip if price is same as US price
      if ($price['currency'] === $usPrice['currency'] && $price['price'] === $usPrice['price']) {
        continue;
      }

      // skip if country is not supported
      if (!CountryHelper::isSupportedCountry($price['country'])) {
        continue;
      }

      // skip if currency is not supported
      if (!CurrencyHelper::isSupportedCurrency($price['currency'])) {
        continue;
      }

      $currency = CurrencyCode::from($price['currency']);
      $amount = ($price['currency'] === 'EUR' && CountryHelper::isEuCountry($price['country'])) ?
        $this->getProPriceForEuCountry($price['country'], $plan->interval) :
        (int)($price['price'] * $priceRate * CurrencyHelper::getDecimalFactor($price['currency']));

      /**
       * find the unit price override for the same currency and amount
       */
      $unitPriceOverride = null;
      foreach ($unitPriceOverrides as $up) {
        if ($up->unitPrice->currencyCode == $currency && $up->unitPrice->amount == $amount) {
          $unitPriceOverride = $up;
          break;
        }
      }
      if ($unitPriceOverride) {
        $unitPriceOverride->countryCodes[] = CountryCode::from($price['country']);
        continue;
      } else {
        $unitPriceOverrides[] = new UnitPriceOverride(
          countryCodes: [CountryCode::from($price['country'])],
          unitPrice: new Money(
            currencyCode: $currency,
            amount: (string)$amount,
          ),
        );
      }
    }

    if ($mode === PaddleOperation::CREATE) {
      return new CreatePrice(
        description: $plan->description,
        productId: $plan->product->getMeta()->paddle->getProductId($plan->getProductInterval()),
        unitPrice: $unitPrice,
        name: trim(str_replace($plan->product_name, '', $plan->name)),
        type: CatalogType::Standard(),
        unitPriceOverrides: $unitPriceOverrides,
        taxMode: TaxMode::External(),
        trialPeriod: null,
        billingCycle: new TimePeriod(
          interval: Interval::from($plan->interval),
          frequency: $plan->interval_count,
        ),
        quantity: new PriceQuantity(1, 1),
        customData: $customData,
      );
    } else {
      return new UpdatePrice(
        description: $plan->description,
        unitPrice: $unitPrice,
        name: trim(str_replace($plan->product_name, '', $plan->name)),
        type: CatalogType::Standard(),
        unitPriceOverrides: $unitPriceOverrides,
        taxMode: TaxMode::External(),
        trialPeriod: null,
        billingCycle: new TimePeriod(
          interval: Interval::from($plan->interval),
          frequency: $plan->interval_count,
        ),
        quantity: new PriceQuantity(1, 1),
        customData: $customData,
      );
    }
  }

  /**
   * create price from user and billing information
   */
  public function createPaddlePrice(Plan $plan): Price
  {
    $createPrice = $this->prepareData($plan, PaddleOperation::CREATE);
    $paddlePrice = $this->paddleService->createPrice($createPrice);
    $this->result->appendMessage("Paddle price for {$plan->name} created", ['price_id' => $paddlePrice->id], location: __FUNCTION__);

    $this->updatePlan($plan, $paddlePrice);
    $this->result->appendMessage("Plan {$plan->name} updated with paddle price", ['price_id' => $paddlePrice->id], location: __FUNCTION__);
    return $paddlePrice;
  }

  public function updatePaddlePrice(Plan $plan): Price
  {
    $meta = $plan->getMeta();
    if (!$meta->paddle->price_id) {
      throw new \Exception('Paddle price not exist');
    }

    $updatePrice = $this->prepareData($plan, PaddleOperation::UPDATE);
    $paddlePrice = $this->paddleService->updatePrice($meta->paddle->price_id, $updatePrice);
    $this->result->appendMessage("Paddle price for {$plan->name} updated", ['price_id' => $paddlePrice->id], location: __FUNCTION__);

    $this->updatePlan($plan, $paddlePrice);
    $this->result->appendMessage("Plan {$plan->name} updated with paddle price", ['price_id' => $paddlePrice->id], location: __FUNCTION__);
    return $paddlePrice;
  }

  public function createOrUpdatePaddlePrice(Plan $plan): Price
  {
    return $plan->getMeta()->paddle->price_id ?
      $this->updatePaddlePrice($plan) :
      $this->createPaddlePrice($plan);
  }

  public function updatePlan(Plan $plan, Price|EntitiesPrice $price): Plan
  {
    $priceCustomerData = PriceCustomData::from($price->customData?->data);
    if ($priceCustomerData->license_quantity >= LicensePackage::MIN_QUANTITY) {
      // license package price
      $plan->setMetaPaddleLicensePackageId($priceCustomerData->license_package_id)
        ->setMetaPaddleLicensePriceId($priceCustomerData->license_quantity, $price->id)
        ->save();
    } else {
      // plan price
      $plan->setMetaPaddleProductId($price->productId)
        ->setMetaPaddlePriceId($price->id)
        ->save();
    }
    PaddleMap::createOrUpdate($price->id, Plan::class, $plan->id);
    return $plan;
  }

  /**
   * license prices
   */

  public function createPaddleLicensePrice(Plan $plan, LicensePackage $licensePackage, int $quantity): Price
  {
    $createPrice = $this->prepareData($plan, PaddleOperation::CREATE, $licensePackage, $quantity);
    $paddlePrice = $this->paddleService->createPrice($createPrice);
    $this->result->appendMessage("Paddle license price for {$plan->name}/{$quantity} created", ['price_id' => $paddlePrice->id], location: __FUNCTION__);

    $this->updatePlan($plan, $paddlePrice);
    $this->result->appendMessage("Plan {$plan->name} updated with paddle license price", ['price_id' => $paddlePrice->id], location: __FUNCTION__);

    return $paddlePrice;
  }

  public function updatePaddleLicensePrice(Plan $plan, LicensePackage $licensePackage, int $quantity): Price
  {
    $meta = $plan->getMeta();
    if (!$meta->paddle->license_prices->getPriceId($quantity)) {
      throw new \Exception('Paddle license price not exist');
    }

    $updatePrice = $this->prepareData($plan, PaddleOperation::UPDATE, $licensePackage, $quantity);
    $paddlePrice = $this->paddleService->updatePrice($meta->paddle->license_prices->getPriceId($quantity), $updatePrice);
    $this->result->appendMessage("Paddle license price for {$plan->name}/{$quantity} updated", ['price_id' => $paddlePrice->id], location: __FUNCTION__);

    $this->updatePlan($plan, $paddlePrice);
    $this->result->appendMessage("Plan {$plan->name} updated with paddle license price", ['price_id' => $paddlePrice->id], location: __FUNCTION__);

    return $paddlePrice;
  }

  public function createOrUpdatePaddleLicensePrice(Plan $plan, LicensePackage $licensePackage, int $quantity): Price
  {
    if ($plan->getMeta()->paddle->license_prices->getPriceId($quantity)) {
      return $this->updatePaddleLicensePrice($plan, $licensePackage, $quantity);
    } else {
      return $this->createPaddleLicensePrice($plan, $licensePackage, $quantity);
    }
  }

  /**
   * Remove quantity(s) from meta->paddle->license_prices. Note: This does not remove the price from Paddle.
   *
   * @param Plan $plan
   * @param int[]|int $quantities
   * @return void
   */
  public function removePaddleLicensePrices(Plan $plan, array|int $quantities): void
  {
    if (!is_array($quantities)) {
      $quantities = [$quantities];
    }

    $meta = $plan->getMeta();
    foreach ($quantities as $quantity) {
      $priceId = $meta->paddle->license_prices->getPriceId($quantity);
      if ($priceId) {
        $this->paddleService->archivePrice($priceId);
      }
    }
    $meta->paddle->license_prices->removePriceIds($quantities);
    $plan->setMeta($meta)->save();
    $this->result->appendMessage("Paddle license prices for {$plan->name}/[" . implode(',', $quantities) . "] removed", location: __FUNCTION__);
  }

  /**
   * Synchronize plan with corresponding Paddle and prices
   *
   * please note that this method may invoke many api requests and may take a long time to complete
   *
   * create, update or archive prices
   *
   * @param Plan $plan
   */
  public function syncPlan(Plan $plan): void
  {
    // update or create single license price
    $this->createOrUpdatePaddlePrice($plan);

    //
    // create or update or archive license prices
    //
    $licensePackage = LicensePackage::findStandard();
    $currentQuantities = array_keys($plan->getMeta()->paddle->license_prices->price_ids);
    $newQuantities = array_map(
      fn($priceRate) => $priceRate->quantity,
      $licensePackage?->getPriceTable()->price_list ?? []
    );
    $toRemoveQuantities = array_diff($currentQuantities, $newQuantities);

    // remove prices for quantities that are not in the new price list
    $this->removePaddleLicensePrices($plan, $toRemoveQuantities);

    // create or update prices for new quantities
    foreach ($newQuantities as $quantity) {
      $this->createOrUpdatePaddleLicensePrice($plan, $licensePackage, $quantity);
    }
  }

  /**
   * Archive price(s)
   *
   * @param string[]|string $priceIds The IDs of the prices to archive
   */
  public function archivePrices(array|string $priceIds): void
  {
    if (!is_array($priceIds)) {
      $priceIds = [$priceIds];
    }

    foreach ($priceIds as $priceId) {
      $this->paddleService->archivePrice($priceId);
    }
    $this->result->appendMessage("Paddle prices [" . implode(',', $priceIds) . "] archived", location: __FUNCTION__);
  }
}
