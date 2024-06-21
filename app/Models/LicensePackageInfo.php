<?php

namespace App\Models;

class LicensePackageInfo
{
  public function __construct(
    public int $id,
    public string $type,
    public string $product_name,
    public string $name,
    public int $count,
    public float $discount,
    public string $status
  ) {
  }
}
