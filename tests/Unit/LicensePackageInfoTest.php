<?php

namespace Tests\Unit;

use App\Models\LicensePackage;
use Tests\TestCase;

class LicensePackageInfoTest extends TestCase
{
  public function test_LicensePackageInfoRefresh()
  {
    $license_package_info = [
      'id' => 1,
      'type' => LicensePackage::TYPE_STANDARD,
      'name' => 'Standard License',
      'price_table' => [
        ['quantity' => 10, 'discount' => 10],
        ['quantity' => 20, 'discount' => 20],
        ['quantity' => 30, 'discount' => 30],
      ],
    ];

    $new = LicensePackage::refreshInfo($license_package_info, 0);
    $this->assertEquals(0, $new['quantity']);
    $this->assertEquals(0, $new['price_rate']);

    $new = LicensePackage::refreshInfo($license_package_info, 9);
    $this->assertEquals(9, $new['quantity']);
    $this->assertEquals(
      round(9 * (100 - $license_package_info['price_table'][0]['discount']), 2),
      $new['price_rate']
    );

    $new = LicensePackage::refreshInfo($license_package_info, 10);
    $this->assertEquals(10, $new['quantity']);
    $this->assertEquals(
      round(10 * (100 - $license_package_info['price_table'][0]['discount']), 2),
      $new['price_rate']
    );

    $new = LicensePackage::refreshInfo($license_package_info, 11);
    $this->assertEquals(11, $new['quantity']);
    $this->assertEquals(
      round(
        10 * (100 - $license_package_info['price_table'][0]['discount']) +
          (11 - 10) * (100 - $license_package_info['price_table'][1]['discount']),
        2
      ),
      $new['price_rate']
    );

    $new = LicensePackage::refreshInfo($license_package_info, 21);
    $this->assertEquals(21, $new['quantity']);
    $this->assertEquals(
      round(
        10 * (100 - $license_package_info['price_table'][0]['discount']) +
          (20 - 10) * (100 - $license_package_info['price_table'][1]['discount']) +
          (21 - 20) * (100 - $license_package_info['price_table'][2]['discount']),
        2
      ),
      $new['price_rate']
    );

    $new = LicensePackage::refreshInfo($license_package_info, 31);
    $this->assertEquals(30, $new['quantity']);
    $this->assertEquals(
      round(
        10 * (100 - $license_package_info['price_table'][0]['discount']) +
          (20 - 10) * (100 - $license_package_info['price_table'][1]['discount']) +
          (30 - 20) * (100 - $license_package_info['price_table'][2]['discount']),
        2
      ),
      $new['price_rate']
    );
  }
}
