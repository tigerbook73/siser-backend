<?php

namespace App\Models;


/**
 * Class PlanMetaPaddleLicensePrices
 *
 * @property int $license_package_id
 * @property array<int, string> $price_ids
 * [quantity => price_id]
 */
class PlanMetaPaddleLicensePrices
{
  public int $license_package_id;
  public array $price_ids;

  public function __construct(
    int $license_package_id,
    array $price_ids = []
  ) {
    $this->license_package_id = $license_package_id;
    $this->price_ids = $price_ids;
  }

  static public function from(?array $data): self
  {
    $data = $data ?? [];
    return new self(
      $data['license_package_id'] ?? 0,
      $data['price_ids'] ?? []
    );
  }

  public function toArray(): array
  {
    return [
      'license_package_id' => $this->license_package_id,
      'price_ids'  => $this->price_ids,
    ];
  }

  public function setLicensePackageId(int $license_package_id): self
  {
    $this->license_package_id = $license_package_id;
    return $this;
  }

  public function setPriceId(int $quantity, string $price_id): self
  {
    $this->price_ids[$quantity] = $price_id;
    return $this;
  }

  public function getPriceId(int $quantity): ?string
  {
    return $this->price_ids[$quantity] ?? null;
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

    foreach ($quantities as $quantity) {
      unset($this->price_ids[$quantity]);
    }
    return $this;
  }
}

class PlanMetaPaddle
{
  public ?string $product_id;
  public ?string $price_id;
  public PlanMetaPaddleLicensePrices $license_prices;

  public function __construct(?array $data = null)
  {
    $this->product_id = $data['product_id'] ?? null;
    $this->price_id = $data['price_id'] ?? null;
    $this->license_prices = PlanMetaPaddleLicensePrices::from($data['license_prices'] ?? null);
  }

  static public function from(?array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'product_id' => $this->product_id,
      'price_id'  => $this->price_id,
      'license_prices' => $this->license_prices->toArray(),
    ];
  }
}

class PlanMeta
{
  public PlanMetaPaddle $paddle;

  public function __construct(?array $data = null)
  {
    $this->paddle = PlanMetaPaddle::from($data['paddle'] ?? []);
  }

  static public function from(?array $data = null): PlanMeta
  {
    return new PlanMeta($data);
  }

  public function toArray(): array
  {
    return [
      'paddle' => $this->paddle->toArray(),
    ];
  }
}
