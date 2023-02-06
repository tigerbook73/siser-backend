<?php

namespace App\Models;

use App\Models\Base\Country as BaseCountry;

class Country extends BaseCountry
{
  static protected $attributesOption = [
    'code'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'currency'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'processing_fee_rate'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'explicit_processing_fee' => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'              => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];
}
