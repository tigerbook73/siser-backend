<?php

namespace Tests\Feature;

use App\Models\BillingInfo;
use Tests\DR\DrApiTestCase;
use Tests\Models\Address;
use Tests\Models\BillingInfo as ModelsBillingInfo;

class AccountBillingInfoTestCase extends DrApiTestCase
{
  public string $baseUrl = '/api/v1/account';
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
      'tax_id' => [
        'type'        => 'type',
        'value'       => 'value',
      ],
      'language'      => 'en',
    ];
  }
}
