<?php

namespace App\Models;

use Exception;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class LicensePackagePriceRate
 *
 * This class represents a single valid price in a license package.
 *
 * @property int $quantity
 * @property float $discount  0-100
 */
class LicensePackagePriceRate implements Arrayable
{
  public function __construct(
    public int $quantity,
    public float $price_rate,
  ) {
    $this->validate();
  }

  /**
   * @throws Exception
   */
  public function validate(): void
  {
    if (
      $this->quantity < 1 ||
      $this->quantity > LicensePackage::MAX_COUNT ||
      $this->price_rate < 0
    ) {
      throw new Exception("Invalid price rate.");
    }
  }

  public function toArray(): array
  {
    return [
      'quantity' => $this->quantity,
      'price_rate' => $this->price_rate,
    ];
  }

  public static function from(array $data): LicensePackagePriceRate
  {
    return new LicensePackagePriceRate(
      $data['quantity'],
      $data['price_rate'],
    );
  }
}
