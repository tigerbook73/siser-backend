<?php

namespace App\Services\Paddle;

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
   */
  public function prepareData(Plan $plan, PaddleOperation $mode): CreatePrice|UpdatePrice
  {
    $customData = PriceCustomData::from([
      "product_name"      => $plan->product_name,
      "plan_id"           => $plan->id,
      "plan_name"         => $plan->name,
      "plan_timestamp"    => $plan->updated_at->format('Y-m-d H:i:s'),
    ])->toCustomData();

    $priceList = $plan->price_list;

    // get default price (US) (tax exclusive)
    $usPrice = $plan->getPrice('US');

    $unitPrice = new Money(
      currencyCode: CurrencyCode::from($usPrice['currency']),
      amount: (string)(int)($usPrice['price'] * CurrencyHelper::getDecimalFactor($usPrice['currency'])),
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
        (int)($price['price'] * CurrencyHelper::getDecimalFactor($price['currency']));

      /**
       * find the unit price override for the the same currency and amount
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
        status: $plan->status === Plan::STATUS_ACTIVE ? Status::Active() : Status::Archived(),
      );
    }
  }

  /**
   * create price from user and billing information
   */
  public function createPaddlePrice(Plan $plan): Price
  {
    if ($plan->status !== Plan::STATUS_ACTIVE) {
      throw new \Exception('Plan is not active');
    }

    $createPrice = $this->prepareData($plan, PaddleOperation::CREATE);
    $paddlePrice = $this->paddleService->createPrice($createPrice);
    $this->updatePlan($plan, $paddlePrice);
    return $paddlePrice;
  }

  public function updatePaddlePrice(Plan $plan): Price
  {
    $meta = $plan->getMeta();
    if (!$meta->paddle->price_id) {
      throw new \Exception('Paddle price not exist');
    }

    $updatePrice = $this->prepareData($plan, PaddleOperation::UPDATE);
    return $this->paddleService->updatePrice($meta->paddle->price_id, $updatePrice);
  }

  public function createOrUpdatePaddlePrice(Plan $plan): Price
  {
    return $plan->getMeta()->paddle->price_id ?
      $this->updatePaddlePrice($plan) :
      $this->createPaddlePrice($plan);
  }

  public function updatePlan(Plan $plan, Price|EntitiesPrice $price): Plan
  {
    $plan->setMetaPaddleProductId($price->productId)
      ->setMetaPaddlePriceId($price->id)
      ->save();
    PaddleMap::createOrUpdate($price->id, Plan::class, $plan->id);
    return $plan;
  }
}
