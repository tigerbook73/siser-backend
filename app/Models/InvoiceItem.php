<?php

namespace App\Models;

use App\Models\Paddle\PriceCustomData;
use App\Services\CurrencyHelper;
use Paddle\SDK\Entities\Subscription as PaddleSubscription;
use Paddle\SDK\Entities\Transaction as PaddleTransaction;

class InvoiceItem
{
  public function __construct(
    public string $name,
    public string $currency,
    public float $price,
    public float $discount,
    public float $tax,
    public float $amount,
    public int $quantity,

    public ?int $plan_id,
    public ?int $license_package_id,
    public ?int $license_quantity,
    public ?InvoiceItemMeta $meta,
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
      plan_id: $data['plan_id'] ?? null,
      license_package_id: $data['license_package_id'] ?? null,
      license_quantity: $data['license_quantity'] ?? null,
      meta: isset($data['meta']) ? InvoiceItemMeta::from($data['meta']) : null,
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
      'plan_id' => $this->plan_id,
      'license_package_id' => $this->license_package_id,
      'license_quantity' => $this->license_quantity,
      'meta' => $this->meta ? $this->meta->toArray() : null,
    ];
  }

  /**
   * @param array $data array of items
   * @return InvoiceItem[]
   */
  static function itemsFrom(array $data): array
  {
    return array_map(fn($item) => self::from($item), $data);
  }

  /**
   * build invoice items from Paddle Transaction
   *
   * @param PaddleTransaction $paddleTransaction
   * @return InvoiceItem[]
   */
  static public function buildItems(PaddleTransaction $paddleTransaction): array
  {
    $invoiceItems = [];
    for ($i = 0; $i < count($paddleTransaction->items); $i++) {
      $item = $paddleTransaction->items[$i];
      $lineItem = $paddleTransaction->details->lineItems[$i];
      $priceCustomData = PriceCustomData::from($item->price->customData?->data);

      $invoiceItems[] = new self(
        name: $priceCustomData->plan_name,
        currency: $paddleTransaction->currencyCode->getValue(),
        quantity: $lineItem->quantity,
        price: CurrencyHelper::getDecimalPrice($paddleTransaction->currencyCode->getValue(), $lineItem->totals->subtotal),
        discount: CurrencyHelper::getDecimalPrice($paddleTransaction->currencyCode->getValue(), $lineItem->totals->discount),
        tax: CurrencyHelper::getDecimalPrice($paddleTransaction->currencyCode->getValue(), $lineItem->totals->tax),
        amount: CurrencyHelper::getDecimalPrice($paddleTransaction->currencyCode->getValue(), $lineItem->totals->total),
        plan_id: $priceCustomData->plan_id,
        license_package_id: $priceCustomData->license_package_id,
        license_quantity: $priceCustomData->license_quantity,
        meta: new InvoiceItemMeta(
          product_id: $item->price->productId,
          price_id: $item->price->id,
          proration: $item->proration,
        ),
      );
    }

    // sort by quantity descending (keep the most outstanding items first)
    usort($invoiceItems, fn($a, $b) => $b->quantity - $a->quantity);
    return $invoiceItems;
  }

  /**
   * build next invoice items from Paddle Subscription
   *
   * @return InvoiceItem[]
   */
  static public function buildNextItemsForSubscription(PaddleSubscription $paddleSubscription): array
  {
    $invoiceItems = [];
    for ($i = 0; $i < count($paddleSubscription->nextTransaction?->details->lineItems ?? []); $i++) {
      $lineItem = $paddleSubscription->nextTransaction?->details->lineItems[$i];

      // because nextTransaction does not contain price data, we need to find the plan and license quantity
      $planWithMeta = PaddleMap::findPlanWithMeta($lineItem->priceId);
      $plan = $planWithMeta->model;
      $priceCustomData = PriceCustomData::from(
        $planWithMeta->meta ?? [
          // fallback to default values for backward compatibility with previous version of PaddleMap
          'plan_name' => $plan->name,
          'license_quantity' => 1
        ]
      );

      $invoiceItems[] = new self(
        name: $priceCustomData->plan_name,
        currency: $paddleSubscription->currencyCode->getValue(),
        quantity: $lineItem->quantity,
        price: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->subtotal),
        discount: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->discount),
        tax: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->tax),
        amount: CurrencyHelper::getDecimalPrice($paddleSubscription->currencyCode->getValue(), $lineItem->totals->total),
        plan_id: $plan->id,
        license_package_id: $plan->getMetaPaddleLicensePackageId(),
        license_quantity: $priceCustomData->license_quantity,
        meta: new InvoiceItemMeta(
          product_id: $lineItem->product->id,
          price_id: $lineItem->priceId,
          proration: null,
        ),
      );
    }
    return $invoiceItems;
  }
}
