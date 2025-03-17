<?php

namespace App\Models;

class PlanMetaPaddleLicensePrices
{
  /**
   * @param PlanMetaPaddleLicensePrice[] $price_ids
   */
  public function __construct(
    public int $license_package_id,
    private array $price_ids
  ) {
    usort($this->price_ids, fn($a, $b) => $a->quantity - $b->quantity);
  }

  static public function from(array $data): self
  {
    return new self(
      license_package_id: $data['license_package_id'] ?? 0,
      price_ids: array_map(
        fn($price) => PlanMetaPaddleLicensePrice::from($price),
        $data['price_ids'] ?? []
      )
    );
  }

  public function toArray(): array
  {
    return [
      'license_package_id' => $this->license_package_id,
      'price_ids'  => array_map(fn($price) => $price->toArray(), $this->price_ids),
    ];
  }

  public function setLicensePackageId(int $license_package_id): self
  {
    $this->license_package_id = $license_package_id;
    return $this;
  }

  public function setPriceId(int $quantity, string $price_id): self
  {
    // if found
    foreach ($this->price_ids as $price) {
      if ($price->quantity === $quantity) {
        $price->price_id = $price_id;
        return $this;
      }
    }

    // if not found
    $this->price_ids[] = new PlanMetaPaddleLicensePrice(
      quantity: $quantity,
      price_id: $price_id,
    );

    usort($this->price_ids, fn($a, $b) => $a->quantity - $b->quantity);
    return $this;
  }

  public function getPriceId(int $quantity): ?string
  {
    foreach ($this->price_ids as $price) {
      if ($price->quantity === $quantity) {
        return $price->price_id;
      }
    }
    return null;
  }

  /**
   * Remove quantity(s) from this->price_ids. Note: This does not remove the price from Paddle.
   *
   * @param int[]|int $quantities
   * @return self
   */
  public function removePriceIds(array|int $quantities): self
  {
    if (!is_array($quantities)) {
      $quantities = [$quantities];
    }

    $this->price_ids = array_filter(
      $this->price_ids,
      fn($price) => !in_array($price->quantity, $quantities)
    );
    return $this;
  }

  /**
   * @return int[]
   */
  public function getQuantities(): array
  {
    return array_map(fn($price) => $price->quantity, $this->price_ids);
  }

  /**
   * @return string[]
   */
  public function getPriceIds(): array
  {
    return array_map(fn($price) => $price->price_id, $this->price_ids);
  }
}
