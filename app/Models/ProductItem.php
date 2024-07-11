<?php

namespace App\Models;

use DigitalRiver\ApiSdk\Model\Checkout as DrCheckout;
use DigitalRiver\ApiSdk\Model\Invoice as DrInvoice;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\ProductDetails as DrProductDetails;

class ProductItem
{
  // subscription item category
  public const ITEM_CATEGORY_PLAN             = 'plan';
  public const ITEM_CATEGORY_LICENSE          = 'license';

  static public function buildPlanName(array $plan_info, array|null $coupon_info): string
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

  static public function calcPlanPrice(array $plan_info, array|null $coupon_info): float
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

  /**
   * @return array[]
   */
  static public function buildItems(array $plan_info, array $coupon_info = null, array $license_package_info = null): array
  {
    $items = [];
    $planPrice = self::calcPlanPrice($plan_info, $coupon_info);
    $itemPlan = [
      'category'    => self::ITEM_CATEGORY_PLAN,
      'name'        => self::buildPlanName($plan_info, $coupon_info),
      'quantity'    => 1,
      'price'       => $planPrice,
      'tax'         => 0, // tax is calculated later
      'amount'      => $planPrice,
    ];
    $items[] = $itemPlan;

    if ($license_package_info) {
      $licensePrice = round($planPrice * $license_package_info['price_rate'] / 100, 2);
      $itemLicense = [
        'category'  => self::ITEM_CATEGORY_LICENSE,
        'name'      => $license_package_info['name'] . ' x ' . $license_package_info['quantity'],
        'quantity'  => 1,
        'price'     => $licensePrice,
        'tax'       => 0, // tax is calculated later
        'amount'    => $licensePrice,
      ];
      $items[] = $itemLicense;
    }
    return $items;
  }

  /**
   * @return array[]
   */
  static public function buildItemsFromDrObject(DrCheckout|DrOrder|DrInvoice $drObject): array
  {
    $items = [];
    foreach ($drObject->getItems() as $drItem) {
      $category = $drItem->getProductDetails()->getDescription() ?: (str_contains($drItem->getProductDetails()->getName(), 'Plan') ?
        self::ITEM_CATEGORY_PLAN :
        self::ITEM_CATEGORY_LICENSE);
      $item = [
        'category'  => $category,
        'name'      => $drItem->getProductDetails()->getName(),
        'quantity'  => $drItem->getQuantity(),
        'price'     => $drItem->getAmount(),
        'tax'       => $drItem->getTax()->getAmount(),
        'amount'    => $drItem->getAmount(),
      ];
      $items[] = $item;
    }

    // make the plan item first if possible
    if (
      count($items) >= 2 &&
      $items[0]['category'] === self::ITEM_CATEGORY_LICENSE &&
      $items[1]['category'] === self::ITEM_CATEGORY_PLAN
    ) {
      $temp = $items[0];
      $items[0] = $items[1];
      $items[1] = $temp;
    }
    return $items;
  }

  /**
   * @return array[]
   */
  static public function rebuildItemsForTax(array $items, float $taxRate, array $prevItems = null,): array
  {
    $updatedItems = [];
    foreach ($items as $index => $item) {
      if (isset($prevItems[$index]['price']) && $item['price'] === $prevItems[$index]['price']) {
        $item['tax'] = $prevItems[$index]['tax'];
      } else {
        $item['tax'] = round($item['price'] * $taxRate, 2);
      }
      $updatedItems[] = $item;
    }
    return $updatedItems;
  }

  static public function calcTotal(array $items, string $field): float
  {
    return round(array_reduce($items, fn ($carry, $item) => $carry + $item[$field], 0), 2);
  }

  /**
   * @param array[] $items
   */
  static public function findItem(array $items, string $category): array|null
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
  static public function findPlanItem(array $items): array|null
  {
    return self::findItem($items, self::ITEM_CATEGORY_PLAN);
  }

  /**
   * @param array[] $items
   */
  static public function findLicenseItem(array $items): array|null
  {
    return self::findItem($items, self::ITEM_CATEGORY_LICENSE);
  }

  /**
   * fill DrProductDetails with item data
   */
  static public function fillDrProductDetails(DrProductDetails $drProductDetails, array $item): DrProductDetails
  {
    $drProductDetails
      ->setSkuGroupId(config('dr.sku_grp_subscription'))
      ->setDescription($item['category'])
      ->setName($item['name'])
      ->setCountryOfOrigin('AU');
    return $drProductDetails;
  }

  /**
   * build DrProductDetails from item data
   */
  static public function BuildDrProductDetails(array $item): DrProductDetails
  {
    return self::fillDrProductDetails(new DrProductDetails(), $item);
  }
}
