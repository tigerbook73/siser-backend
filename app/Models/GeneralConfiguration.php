<?php

namespace App\Models;

use App\Models\Base\GeneralConfiguration as BaseGeneralConfiguration;

class GeneralConfiguration extends BaseGeneralConfiguration
{
  public static function getMachineLicenseUnit()
  {
    return static::where('name', 'machine_license_unit')->first()->value;
  }
}
