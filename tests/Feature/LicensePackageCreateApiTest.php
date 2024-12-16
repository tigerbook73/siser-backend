<?php

namespace Tests\Feature;

use App\Models\LicensePackage;
use App\Models\LicensePlan;
use PharIo\Manifest\License;

class LicensePackageCreateApiTest extends LicensePackageTestCase
{
  public ?string $role = 'admin';

  protected function setUp(): void
  {
    parent::setUp();

    // remove existing data
    LicensePlan::query()->delete();
    LicensePackage::query()->delete();
  }

  public function testLicensePackageCreateOk()
  {
    $this->createAssert();
  }

  public function testLicensePackageCreateNotSorted()
  {
    $this->hidden[] = 'price_table';

    usort($this->modelCreate['price_table'], function ($a, $b) {
      return $b['quantity'] - $a['quantity'];
    });
    $this->createAssert();
  }

  public function textLicensePackageCreateDuplicatedType()
  {
    $this->createAssert();
    $this->createAssert(400);
  }

  public function testLicensePackageCreateInvalidPriceTable()
  {
    // count not set
    $this->modelCreate['price_table'] = [
      'discount' => 90,
    ];
    $this->createAssert(400);

    // count too small
    $this->modelCreate['price_table'] = [
      'quantity' => 0,
      'discount' => 90,
    ];
    $this->createAssert(400);

    // count too large
    $this->modelCreate['price_table'] = [
      'quantity' => LicensePackage::MAX_COUNT + 1,
      'discount' => 90,
    ];
    $this->createAssert(400);

    // discount not set
    $this->modelCreate['price_table'] = [
      'quantity' => 90,
    ];
    $this->createAssert(400);

    // discount too small
    $this->modelCreate['price_table'] = [
      'quantity' => 10,
      'discount' => -1,
    ];
    $this->createAssert(400);

    // discount too large
    $this->modelCreate['price_table'] = [
      'quantity' => 10,
      'discount' => 100,
    ];
    $this->createAssert(400);

    // discount decrease
    $this->modelCreate['price_table'] = [
      ['quantity' => 10, 'discount' => 90],
      ['quantity' => 20, 'discount' => 80],
    ];
    $this->createAssert(400);
  }
}
