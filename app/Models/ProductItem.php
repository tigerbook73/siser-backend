<?php

namespace App\Models;

use App\Models\Paddle\ProductCustomData;
use App\Models\PaddleMap;
use App\Models\Product;
use App\Services\CurrencyHelper;
use App\Services\SubscriptionManager\WebhookException;
use Paddle\SDK\Entities\Shared\TransactionDetailsPreview;
use Paddle\SDK\Entities\Subscription as PaddleSubscription;
use Paddle\SDK\Entities\Transaction\TransactionDetails;


class ProductItem
{
  // subscription item category
  public const ITEM_CATEGORY_PLAN             = 'plan';

  static public function buildPlanName(array $plan_info, ?array $coupon_info): string
  {
    // free trial
    if ($coupon_info && $coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      return $coupon_info['name'];
    }

    // percentage off
    if ($coupon_info && $coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_PERCENTAGE) {
      return "{$plan_info['name']} ({$coupon_info['name']})";
    }

    // standard plan
    return $plan_info['name'];
  }

  static public function calcPlanPrice(array $plan_info, ?array $coupon_info): float
  {
    $price = $plan_info['price']['price'];

    if (!$coupon_info) {
      return $price;
    }

    if ($coupon_info['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      return 0;
    }

    return round($price * (100 - $coupon_info['percentage_off']) / 100, 2);
  }

  static public function buildPlanItem(array $plan_info, ?array $coupon_info): array
  {
    $planPrice = self::calcPlanPrice($plan_info, $coupon_info);
    return [
      'category'  => self::ITEM_CATEGORY_PLAN,
      'name'      => self::buildPlanName($plan_info, $coupon_info),
      'quantity'  => 1,
      'price'     => $planPrice,
      'tax'       => 0, // tax is calculated later
      'amount'    => $planPrice,
    ];
  }


  /**
   * @return array[]
   */
  static public function buildItems(array $plan_info, ?array $coupon_info = null, ?array $license_package_info = null, ?float $tax_rate = null, ?array $prev_items = null): array
  {
    $items[] = self::buildPlanItem($plan_info, $coupon_info);
    if ($license_package_info) {
    }

    if ($tax_rate) {
      $items = self::rebuildItemsForTax($items, $tax_rate, $prev_items);
    }
    return $items;
  }

  /**
   * @return array[] $items
   */
  static public function removeItem(array $items, string $category): array
  {
    $updatedItems = [];
    foreach ($items as $item) {
      if ($item['category'] !== $category) {
        $updatedItems[] = $item;
      }
    }
    return $updatedItems;
  }

  /**
   * @return array[]
   */
  static public function rebuildItemsForTax(array $items, float $taxRate, ?array $prevItems = null,): array
  {
    $updatedItems = [];
    foreach ($items as $index => $item) {
      $peerItem = $prevItems ? self::findItem($prevItems, $item['category']) : null;
      if ($peerItem && $item['price'] === $peerItem['price']) {
        $item['tax'] = $peerItem['tax'];
      } else {
        $item['tax'] = round($item['price'] * $taxRate, 2);
      }
      $updatedItems[] = $item;
    }
    return $updatedItems;
  }

  static public function calcTotal(array $items, string $field): float
  {
    return round(array_reduce($items, fn($carry, $item) => $carry + $item[$field], 0), 2);
  }

  /**
   * @param array[] $items
   */
  static public function findItem(array $items, string $category): ?array
  {
    foreach ($items as $item) {
      if ($item['category'] === $category) {
        return $item;
      }
    }
    return null;
  }

  /**
   * @param array[] $items
   */
  static public function findPlanItem(array $items): ?array
  {
    return self::findItem($items, self::ITEM_CATEGORY_PLAN);
  }

  /**
   * sort items
   *
   */
  static public function sortItems(array &$items): void
  {
    usort(
      $items,
      fn($a, $b) => $a['category'] === $b['category'] ?
        $b['quantity'] - $a['quantity'] : ($a['category'] === self::ITEM_CATEGORY_PLAN ? -1 : 1)
    );
  }

  static public function buildItem(
    string $productType,
    string $paddlePriceId,
    int $quantity,
    ?float $price = null,
    ?float $discount = null,
    ?float $tax = null,
    ?float $amount = null
  ): array {
    if ($productType == Product::TYPE_SUBSCRIPTION) {
      $category = ProductItem::ITEM_CATEGORY_PLAN;
      $name = PaddleMap::findPlanByPaddleId($paddlePriceId)?->name;
    } else {
      throw new WebhookException('WebhookException at ' . __FUNCTION__ . ':' . __LINE__);
    }

    return [
      'category'                    => $category,
      'name'                        => $name,
      'quantity'                    => $quantity,
      'price'                       => $price,
      'discount'                    => $discount,
      'tax'                         => $tax,
      'amount'                      => $amount,
      'available_to_refund_amount'  => 0,
    ];
  }

  static public function buildItemsFromPaddleResource(TransactionDetails|TransactionDetailsPreview|PaddleSubscription $paddleResource): array
  {
    $items = [];
    if ($paddleResource instanceof TransactionDetails || $paddleResource instanceof TransactionDetailsPreview) {
      foreach ($paddleResource->lineItems as $lineItem) {
        $productCustomData = ProductCustomData::from($lineItem->product->customData->data);
        $items[] = self::buildItem(
          productType: $productCustomData->product_type,
          paddlePriceId: $lineItem->priceId,
          quantity: $lineItem->quantity,
          price: CurrencyHelper::getDecimalPrice($paddleResource->totals->currencyCode->getValue(), $lineItem->totals->subtotal),
          discount: CurrencyHelper::getDecimalPrice($paddleResource->totals->currencyCode->getValue(), $lineItem->totals->discount),
          tax: CurrencyHelper::getDecimalPrice($paddleResource->totals->currencyCode->getValue(), $lineItem->totals->tax),
          amount: CurrencyHelper::getDecimalPrice($paddleResource->totals->currencyCode->getValue(), $lineItem->totals->total)
        );
      }
    } else {
      foreach ($paddleResource->items as $subscriptionItem) {
        $productCustomData = ProductCustomData::from($subscriptionItem->product->customData->data);
        $items[] = self::buildItem(
          productType: $productCustomData->product_type,
          paddlePriceId: $subscriptionItem->price->id,
          quantity: $subscriptionItem->quantity
        );
      }
    }

    ProductItem::sortItems($items);
    return $items;
  }
}
