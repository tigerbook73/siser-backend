<?php

namespace Tests\Unit;

use App\Models\LicensePackage;
use App\Models\LicensePackagePriceStep;
use Exception;
use Tests\TestCase;

class LicensePackagePriceStepTest extends TestCase
{
  public function testValidPriceStep()
  {
    // Edge case: minimum valid 'from' value
    $step = new LicensePackagePriceStep(
      from: LicensePackage::MIN_QUANTITY,
      to: LicensePackage::MIN_QUANTITY,
      discount: 10.0,
      base_discount: 2.0
    );
    $this->assertNotNull($step);

    // Edge case: maximum valid 'from' value
    $step = new LicensePackagePriceStep(
      from: LicensePackage::MAX_QUANTITY,
      to: LicensePackage::MAX_QUANTITY,
      discount: 10.0,
      base_discount: 2.0
    );
    $this->assertNotNull($step);

    // Edge case: discount at lower boundary
    $step = new LicensePackagePriceStep(
      from: 3,
      to: 8,
      discount: 0,
      base_discount: 1.0
    );
    $this->assertNotNull($step);

    // Edge case: discount at upper boundary
    $step = new LicensePackagePriceStep(
      from: 3,
      to: 8,
      discount: 99.99,
      base_discount: 1.0
    );
    $this->assertNotNull($step);

    // Edge case: base_discount at lower boundary
    $step = new LicensePackagePriceStep(
      from: 3,
      to: 8,
      discount: 99.99,
      base_discount: 0.0
    );
    $this->assertNotNull($step);
  }

  public function testInvalidPriceStepFrom()
  {
    // Edge case: 'from' value less than minimum
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: LicensePackage::MIN_QUANTITY - 1, // explicitly testing a value below the minimum
      to: 10,
      discount: 15.5,
      base_discount: 5.0
    ); // from < MIN_COUNT

    // Edge case: 'from' value greater than 'to' value
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: 10,
      to: 9, // explicitly testing a value less than 'from'
      discount: 15.5,
      base_discount: 5.0
    ); // from > to

    // Edge case: 'from' value greater than maximum
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: LicensePackage::MAX_QUANTITY + 1, // explicitly testing a value above the maximum
      to: 10,
      discount: 15.5,
      base_discount: 5.0
    ); // from > MAX_COUNT
  }

  public function testInvalidPriceStepTo()
  {
    // Edge case: 'to' value greater than maximum
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: 2,
      to: LicensePackage::MAX_QUANTITY + 1, // explicitly testing a value above the maximum
      discount: 15.5,
      base_discount: 5.0
    ); // to > MAX_COUNT
  }

  public function testInvalidPriceStepDiscount()
  {
    // Edge case: discount less than lower boundary
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: 2,
      to: 10,
      discount: -0.01, // explicitly testing a value below the lower boundary
      base_discount: 5.0
    ); // discount < 0

    // Edge case: discount greater than or equal to upper boundary
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: 2,
      to: 10,
      discount: 100.0, // explicitly testing a value above the upper boundary
      base_discount: 5.0
    ); // discount >= 100
  }

  public function testInvalidPriceStepBaseDiscount()
  {
    // Edge case: base_discount less than lower boundary
    $this->expectException(Exception::class);
    new LicensePackagePriceStep(
      from: 2,
      to: 10,
      discount: 15.5,
      base_discount: -0.01 // explicitly testing a value below the lower boundary
    ); // base_discount < 0
  }

  public function testToArray()
  {
    $step = new LicensePackagePriceStep(2, 10, 15.5, 5.0);
    $array = $step->toArray();
    $this->assertEquals([
      'from' => 2,
      'to' => 10,
      'discount' => 15.5,
      'base_discount' => 5.0,
    ], $array);
  }

  public function testFromArray()
  {
    $data = [
      'from' => 2,
      'to' => 10,
      'discount' => 15.5,
      'base_discount' => 5.0,
    ];
    $step = LicensePackagePriceStep::from($data);
    $this->assertEquals(2, $step->from);
    $this->assertEquals(10, $step->to);
    $this->assertEquals(15.5, $step->discount);
    $this->assertEquals(5.0, $step->base_discount);
  }

  public function testBackwardCompatibility()
  {
    $data = [
      'quantity' => 10,
      'discount' => 15.5,
    ];
    $step = LicensePackagePriceStep::from($data);
    $this->assertEquals(LicensePackage::MIN_QUANTITY, $step->from);
    $this->assertEquals(10, $step->to);
    $this->assertEquals(15.5, $step->discount);
    $this->assertEquals(0, $step->base_discount);
  }
}
