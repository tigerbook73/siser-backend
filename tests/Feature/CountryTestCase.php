<?php

namespace Tests\Feature;

use App\Models\Country;
use Tests\ApiTestCase;
use Tests\Models\Country as ModelsCountry;

class CountryTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/countries';
  public string $model = Country::class;


  public Country $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = array_keys((array)new ModelsCountry);;

    $this->modelCreate = [
      'code' => "UK",
      'name' => "United Kingtom",
      'currency' => "GBP",
      'processing_fee_rate' => 2,
      'explicit_processing_fee' => false,
    ];

    $this->modelUpdate = [
      // 'code' => "US",
      'name' => "USA",
      'currency' => "USD",
      'processing_fee_rate' => 3,
      'explicit_processing_fee' => false,
    ];

    $this->object = Country::first();
  }
}
