<?php

namespace Tests\Feature;

use App\Models\LicensePackage;

class LicensePackageCreateApiTest extends LicensePackageTestCase
{
  public ?string $role = 'admin';

  protected function setUp(): void
  {
    parent::setUp();

    // remove existing data
    LicensePackage::query()->delete();
  }

  public function testLicensePackageCreateOk()
  {
    $this->createAssert();
  }

  public function testLicensePackageCreateNotSorted()
  {
    $this->hidden[] = 'price_table';

    usort($this->modelCreate['price_table']['price_steps'], function ($a, $b) {
      return $b['from'] - $a['from'];
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
    // from/to not set
    $this->modelCreate['price_table']['price_steps'] = [
      ['discount' => 90],
    ];
    $this->createAssert(400);

    // invalid from/to
    $this->modelCreate['price_table']['price_steps'] = [
      ['from' => LicensePackage::MIN_QUANTITY - 1, 'to' => 10, 'discount' => 10],
    ];
    $this->createAssert(400);

    // count too large
    $this->modelCreate['price_table']['price_steps'] = [
      ['from' => LicensePackage::MIN_QUANTITY, 'to' => LicensePackage::MAX_QUANTITY + 1, 'discount' => 90,]
    ];
    $this->createAssert(400);

    // discount not set
    $this->modelCreate['price_table']['price_steps'] = [
      ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10],
    ];
    $this->createAssert(400);

    // discount too small
    $this->modelCreate['price_table']['price_steps'] = [
      ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10, 'discount' => -1],
    ];
    $this->createAssert(400);

    // discount too large
    $this->modelCreate['price_table'] = [
      'price_steps' => [
        ['from' => LicensePackage::MIN_QUANTITY, 'to' => 20, 'discount' => 90],
        ['from' => 21, 'to' => 30, 'discount' => 80],
      ],
      'range' => [[LicensePackage::MIN_QUANTITY, 30]],
    ];
    $this->createAssert(400);

    // discount decrease
    $this->modelCreate['price_table'] = [
      'price_steps' => [
        ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10, 'discount' => 90],
        ['from' => 11, 'to' => 20, 'discount' => 80],
      ],
      'range' => [[LicensePackage::MIN_QUANTITY, 30]],
    ];
    $this->createAssert(400);

    // range invalid start
    $this->modelCreate['price_table'] = [
      'price_steps' => [
        ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10, 'discount' => 10],
      ],
      'range' => [[LicensePackage::MIN_QUANTITY - 1, 10]],
    ];

    // range invalid unit
    $this->modelCreate['price_table'] = [
      'price_steps' => [
        ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10, 'discount' => 10],
      ],
      'range' => [[LicensePackage::MIN_QUANTITY, 10], [15, 10]],
    ];

    // range invalid overlap
    $this->modelCreate['price_table'] = [
      'price_steps' => [
        ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10, 'discount' => 10],
      ],
      'range' => [[LicensePackage::MIN_QUANTITY, 5], [6, 10]],
    ];
  }
}
