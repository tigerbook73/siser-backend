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

  public function info(int $quantity): array
  {
    $priceTable = $this->getPriceTable();
    $priceRate = $priceTable->getPriceRate($quantity);

    return [
      'id'          => $this->id,
      'type'        => $this->type,
      'name'        => $this->name,
      'price_table' => $this->price_table,
      'quantity'    => $quantity,
      'price_rate'  => $priceRate,
    ];
  }

  static public function validatePriceTable(array $priceTable): ?array
  {
    try {
      return LicensePackagePriceTable::from($priceTable)->toArray();
    } catch (\Exception $e) {
      return null;
    }
  }

  public function setPriceTable(LicensePackagePriceTable $priceTable): self
  {
    $this->price_table = $priceTable->toArray();
    return $this;
  }

  public function getPriceTable(): LicensePackagePriceTable
  {
    return LicensePackagePriceTable::from($this->price_table);
  }
}
