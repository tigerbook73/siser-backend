<?php

namespace Tests\Feature;

use App\Models\BillingInfo;
use Tests\ApiTestCase;
use Tests\Models\Address;
use Tests\Models\BillingInfo as ModelsBillingInfo;

class UserBillingInfoTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/users';
  public string $model = BillingInfo::class;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsBillingInfo);
    $this->modelSchema['address'] = array_keys((array)new Address);

    $this->modelUpdate = [
      'first_name'    => 'first_name',
      'last_name'     => 'last_name',
      'phone'         => '',
      'organization'  => '',
      'email'         => 'test-case@me.com',
      'address' => [
        'line1'       => 'line1',
        'line2'       => '',
        'city'        => 'city',
        'postcode'    => 'postcode',
        'state'       => 'state',
        'country'     => 'AU',
      ],
    ];
  }
}
