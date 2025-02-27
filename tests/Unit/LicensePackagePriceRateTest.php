<?php

namespace Tests\Unit;

use App\Models\LicensePackage;
use App\Models\LicensePackagePriceRate;
use Exception;
use Tests\TestCase;

class LicensePackagePriceRateTest extends TestCase
{
  public function testValidPriceRate()
  {
    $rate = new LicensePackagePriceRate(quantity: 5, price_rate: 20.0);
    $this->assertEquals(5, $rate->quantity);
    $this->assertEquals(20.0, $rate->price_rate);

    // Edge case: quantity equals 1
    $rate = new LicensePackagePriceRate(quantity: 1, price_rate: 20.0);
    $this->assertNotNull($rate);

    // Edge case: quantity equals MAX_COUNT
    $rate = new LicensePackagePriceRate(quantity: LicensePackage::MAX_QUANTITY, price_rate: 20.0);
    $this->assertNotNull($rate);

    // Edge case: price_rate equals 0
    $rate = new LicensePackagePriceRate(quantity: 5, price_rate: 0.0);
    $this->assertNotNull($rate);
  }

  public function testInvalidPriceRateQuantityLess()
  {
    // Edge case: quantity less than 1
    $this->expectException(Exception::class);
    new LicensePackagePriceRate(quantity: 0, price_rate: 20.0);
  }

  public function testInvalidPriceRateQuantityLarger()
  {
    // Edge case: quantity greater than MAX_COUNT
    $this->expectException(Exception::class);
    new LicensePackagePriceRate(quantity: LicensePackage::MAX_QUANTITY + 1, price_rate: 20.0);
  }

  public function testToArray()
  {
    $rate = new LicensePackagePriceRate(quantity: 5, price_rate: 20.0);
    $array = $rate->toArray();
    $this->assertEquals([
      'quantity' => 5,
      'price_rate' => 20.0,
    ], $array);
  }

  public function testFromArray()
  {
    $data = [
      'quantity' => 5,
      'price_rate' => 20.0,
    ];
    $rate = LicensePackagePriceRate::from($data);
    $this->assertEquals(5, $rate->quantity);
    $this->assertEquals(20.0, $rate->price_rate);
  }
}
