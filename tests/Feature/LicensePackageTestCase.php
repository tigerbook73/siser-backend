<?php

namespace Tests\Feature;

use App\Models\LicensePackage;
use Tests\ApiTestCase;
use Tests\Models\LicensePackage as ModelsLicensePackage;

class LicensePackageTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/license-packages';
  public string $model = LicensePackage::class;

  public LicensePackage $object;

  protected function setUp(): void
  {
    parent::setUp();

    // remove existing data
    LicensePackage::query()->delete();

    $this->modelSchema = array_keys((array)new ModelsLicensePackage);

    $this->modelCreate = [
      'type'                    => LicensePackage::TYPE_STANDARD,
      'name'                    => 'test-create',
      'price_table'             => [
        'price_steps' => [
          ['from' => LicensePackage::MIN_QUANTITY, 'to'  => 10, 'discount' => 10],
          ['from' => 11, 'to' => 20, 'discount' => 20],
          ['from' => 21, 'to' => 30, 'discount' => 30],
          ['from' => 31, 'to' => LicensePackage::MAX_QUANTITY, 'discount' => 80],
        ],
        'range'                  => [[LicensePackage::MIN_QUANTITY, 30], [31, 31], [50, 50]],
      ],
      'status'                  => LicensePackage::STATUS_ACTIVE,
    ];

    $this->modelUpdate = [
      'name'                    => 'test-update',
      'price_table'             => [
        'price_steps' => [
          ['from' => LicensePackage::MIN_QUANTITY, 'to'  => 10, 'discount' => 10],
          ['from' => 11, 'to' => 20, 'discount' => 20],
          ['from' => 21, 'to' => 30, 'discount' => 30],
          ['from' => 31, 'to' => LicensePackage::MAX_QUANTITY, 'discount' => 80],
        ],
        'range'                  => [[LicensePackage::MIN_QUANTITY, 31]],
      ],
      'status'                  => LicensePackage::STATUS_INACTIVE,
    ];

    $createData = [
      'type'                    => LicensePackage::TYPE_STANDARD,
      'name'                    => 'test-pre-create',
      'price_table'             => [
        'price_steps' => [
          ['from' => LicensePackage::MIN_QUANTITY, 'to' => 10, 'discount' => 10],
        ],
        'range'                  => [[LicensePackage::MIN_QUANTITY, 10]],
      ],
      'status'                  => LicensePackage::STATUS_ACTIVE,
    ];
    $this->object = LicensePackage::create($createData);
  }
}
