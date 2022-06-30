<?php

namespace App\Models;

use App\Models\Base\GeneralConfiguration as BaseGeneralConfiguration;

class GeneralConfiguration extends BaseGeneralConfiguration
{
  static protected $attributesOption = [
    'machine_license_unit' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
  ];
}
