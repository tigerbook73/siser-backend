<?php

namespace App\Models;

use App\Models\Paddle\PriceCustomData;
use App\Services\CurrencyHelper;
use Paddle\SDK\Entities\Subscription as PaddleSubscription;

class SubscriptionItem
{
  /**
   * $price, $discount, $tax, $amount, $quantity are recurring values that after discount ended (if discount is not permanent)
   */
  public function __construct(
    public string $name,
    public string $currency,
    public float $price,
    public float $discount,
    public float $tax,
    public float $amount,
    public int $quantity,
  ) {}

  static function from(array $data): self
  {
    return new self(
      name: $data['name'] ?? '',
      currency: $data['currency'] ?? '',
      price: $data['price'] ?? 0.0,
      discount: $data['discount'] ?? 0.0,
      tax: $data['tax'] ?? 0.0,
      amount: $data['amount'] ?? 0.0,
      quantity: $data['quantity'] ?? 1,
    );
  }

  public function toArray(): array
  {
    return [
      'name' => $this->name,
      'currency' => $this->currency,
      'price' => $this->price,
      'discount' => $this->discount,
      'tax' => $this->tax,
      'amount' => $this->amount,
      'quantity' => $this->quantity,
    ];
  }

  public function validate(): bool
  {
    if (
      empty($this->name) ||
      empty($this->currency) ||
      $this->price < 0 ||
      $this->discount < 0 ||
      $this->tax < 0 ||
      $this->amount < 0 ||
      $this->quantity < 0 ||
      $this->quantity <= 0
    ) {
      return false;
    }
    return true;
  }

  /**
   * @param array $data array of items
   * @return SubscriptionItem[]
   */
  static function itemsFrom(array $data): array
  {
    return array_map(fn($item) => self::from($item), $data);
  }

  /**
   * build subscription items from Paddle Subscription
   * - $paddleSubscription->recurringTransactionDetails must be present
   *
   * @param PaddleSubscription $paddleSubscription
   * @return SubscriptionItem[]
   */
  static public function buildItems(PaddleSubscription $paddleSubscription): array
  {
    $subscriptionItems = [];
    for ($i = 0; $i < count($paddleSubscription->items); $i++) {
      $item = $paddleSubscription->items[$i];
      $lineItem = $paddleSubscription->recurringTransactionDetails?->lineItems[$i];
      $priceCustomData = PriceCustomData::from($item->price->customData?->data);

      $subscriptionItems[] = new self(
        name: $priceCustomData->plan_name,
        currency: $paddleSubscription->currencyCode->getValue(),
        quantity: $lineItem->quantity,
        price: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->subtotal),
        discount: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->discount),
        tax: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->tax),
        amount: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->total)
      );
    }
    return $subscriptionItems;
  }

  /**
   * build subscription items for testing
   *
   * @param PlanInfo $planInfo
   * @param ?CouponInfo $couponInfo
   * @param ?LicensePackageInfo $licensePackageInfo
   * @return SubscriptionItem[]
   */
  static public function buildItemsForTest(
    PlanInfo $planInfo,
    ?CouponInfo $couponInfo = null,
    ?LicensePackageInfo $licensePackageInfo = null,
    float $taxRate = 0.0
  ): array {
    $plan = Plan::findById($planInfo->id);

    $price = $planInfo->price->price * ($licensePackageInfo ? $licensePackageInfo->price_rate->price_rate / 100 : 1);
    $discount = $price * ($couponInfo ? (100 - $couponInfo->percentage_off) / 100 : 0);
    $tax = $price * $taxRate;
    $amount = $price - $discount + $tax;
    $item = new self(
      name: $plan->buildPlanName($licensePackageInfo?->price_rate->quantity ?? 1),
      currency: $planInfo->price->currency,
      price: $planInfo->price->price *
        ($licensePackageInfo ? $licensePackageInfo->price_rate->price_rate / 100 : 1) *
        ($couponInfo ? (100 - $couponInfo->percentage_off) / 100 : 1),
      discount: $discount,
      tax: $tax,
      amount: $amount,
      quantity: 1,
    );

    return [$item];
  }
}
