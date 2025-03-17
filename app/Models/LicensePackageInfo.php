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
