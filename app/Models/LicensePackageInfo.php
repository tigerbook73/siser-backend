<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;

class LicensePackageInfo implements Arrayable
{
  public function __construct(
    public int $id,
    public string $type,
    public string $name,
    public LicensePackagePriceRate $price_rate,
  ) {}

  static public function from(array $data): LicensePackageInfo
  {
    // backward compatibility
    // if $data['quantity'] and $data['price_rate'] are set and $data['price_rate'] is numeric
    if (isset($data['quantity']) && isset($data['price_rate']) && is_numeric($data['price_rate'])) {
      $data['price_rate'] = [
        'quantity' => $data['quantity'],
        'price_rate' => $data['price_rate'],
      ];
    }

    return new LicensePackageInfo(
      $data['id'],
      $data['type'],
      $data['name'],
      LicensePackagePriceRate::from($data['price_rate']),
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'type' => $this->type,
      'name' => $this->name,
      'price_rate' => $this->price_rate->toArray(),
    ];
  }
}
