<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class LicensePackagePriceStep
 *
 * This class represents a step in the license package table.
 * license quantity from $this.from to $this.to will have a discount of this.discount
 *
 * This class is convertable from array to object and vice versa
 *
 * @property int $from min: LicensePackage::MIN_QUANTITY (2)
 * @property int $to max: LicensePackage::MAX_QUANTITY
 * @property float $discount  0-100
 * @property float $base_discount  // the total discount from the previous steps
 */
class LicensePackagePriceStep implements Arrayable
{
  public function __construct(
    public int $from,
    public int $to,
    public float $discount,
    public float $base_discount = 0
  ) {
    $this->validate();
  }

  public function validate()
  {
    if (
      $this->from < LicensePackage::MIN_QUANTITY ||
      $this->from > $this->to ||
      $this->to > LicensePackage::MAX_QUANTITY ||
      $this->discount < 0 ||
      $this->discount >= 100 ||
      $this->base_discount < 0
    ) {
      throw new Exception("Invalid price step.");
    }
  }

  public function toArray(): array
  {
    return [
      'from'      => $this->from,
      'to'        => $this->to,
      'discount'  => $this->discount,
      'base_discount' => $this->base_discount,
    ];
  }

  public static function from(array $data): LicensePackagePriceStep
  {
    // backward compatibility for old price table
    if (isset($data['quantity'])) {
      $data['from'] = LicensePackage::MIN_QUANTITY;
      $data['to'] = (int)$data['quantity'];
    }

    return new LicensePackagePriceStep(
      (int)$data['from'],
      (int)$data['to'],
      (float)$data['discount'],
      (float)($data['base_discount'] ?? 0)
    );
  }
}
