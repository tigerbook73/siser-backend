<?php

namespace App\Models;

use App\Models\Base\LicensePackage as BaseLicensePackage;

class LicensePackage extends BaseLicensePackage
{
  const TYPE_STANDARD     = 'standard';
  const TYPE_EDUCATION    = 'education';

  const STATUS_ACTIVE     = 'active';
  const STATUS_INACTIVE   = 'inactive';

  const MAX_COUNT         = 300;

  static protected $attributesOption = [
    'id'                  => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'type'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'price_table'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function isStandard(): bool
  {
    return $this->type === self::TYPE_STANDARD;
  }

  public function isEducation(): bool
  {
    return $this->type === self::TYPE_EDUCATION;
  }

  public function setTypeStandard(): self
  {
    $this->type = self::TYPE_STANDARD;
    return $this;
  }

  public function setTypeEducation(): self
  {
    $this->type = self::TYPE_EDUCATION;
    return $this;
  }

  public function isActive(): bool
  {
    return $this->status === self::STATUS_ACTIVE;
  }

  public function isInactive(): bool
  {
    return $this->status === self::STATUS_INACTIVE;
  }

  public function setStatusActive(): self
  {
    $this->status = self::STATUS_ACTIVE;
    return $this;
  }

  public function setStatusInactive(): self
  {
    $this->status = self::STATUS_INACTIVE;
    return $this;
  }

  static public function refreshInfo(array $license_package_info, int $quantity): array
  {
    $priceRate = 0;
    if ($quantity <= 0) {
      $priceRate  = 0;
      $quantity   = 0;
    } else {
      $priceRate = 0;
      $lastQuantity = 0;
      foreach ($license_package_info['price_table'] as $priceStep) {
        if ($quantity <= $priceStep['quantity']) {
          $priceRate += ($quantity - $lastQuantity) * (100 - $priceStep['discount']);
          $lastQuantity = $quantity;
          break;
        }
        $priceRate += ($priceStep['quantity'] - $lastQuantity) * (100 - $priceStep['discount']);
        $lastQuantity = $priceStep['quantity'];
      }
      $quantity = $lastQuantity;
    }

    return [
      'id'          => $license_package_info['id'],
      'type'        => $license_package_info['type'],
      'name'        => $license_package_info['name'],
      'price_table' => $license_package_info['price_table'],
      'quantity'    => $quantity,
      'price_rate'  => $priceRate,
    ];
  }

  public function info(int $quantity): array
  {
    $info = [
      'id'          => $this->id,
      'type'        => $this->type,
      'name'        => $this->name,
      'price_table' => $this->price_table,
      'quantity'    => $quantity,
      'price_rate'  => 0,
    ];

    return self::refreshInfo($info, $quantity);
  }

  public function getMaxQuantity(): int
  {
    $priceTable = $this->price_table;
    return $priceTable[count($priceTable) - 1]['quantity'];
  }

  static public function validatePriceTable(array $priceTable): ?array
  {
    /**
     * structure of price:
     * [
     *  ['quantity' => 5,  'discount' => 10],
     *  ['quantity' => 10, 'discount' => 20],
     *  ['quantity' => (<=MAX_COUNT), 'discount' => 40]
     * ]
     *
     * 1. sort the price array by count field
     * 2. validate the price array
     *  a. count > 0
     *  b. discount >= 0 < 100
     *  c. count > previous count
     *  d. discount > previous discount
     *  e. the last count should be less or equal than self::MAX_COUNT
     */

    //  default price step
    if (count($priceTable) === 0) {
      return null;
    }

    foreach ($priceTable as $index => $priceStep) {
      if (!isset($priceStep['quantity']) || !isset($priceStep['discount'])) {
        return null;
      }
    }

    // validate price
    $sortedPriceTable = $priceTable;
    usort($sortedPriceTable, function ($a, $b) {
      return $a['quantity'] - $b['quantity'];
    });
    foreach ($sortedPriceTable as $index => $priceStep) {
      if ($priceStep['quantity'] <= 0 || $priceStep['discount'] < 0 || $priceStep['discount'] >= 100) {
        return null;
      }
      if ($index > 0 && $priceStep['quantity'] <= $sortedPriceTable[$index - 1]['quantity']) {
        return null;
      }
      if ($index > 0 && $priceStep['discount'] < $sortedPriceTable[$index - 1]['discount']) {
        return null;
      }
      if ($index === count($sortedPriceTable) - 1 && $priceStep['quantity'] > self::MAX_COUNT) {
        return null;
      }
    }

    return $sortedPriceTable;
  }
}
