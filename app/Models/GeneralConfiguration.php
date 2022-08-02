<?php

namespace App\Models;

use App\Models\Base\GeneralConfiguration as BaseGeneralConfiguration;
use Illuminate\Support\Facades\DB;

class GeneralConfiguration extends BaseGeneralConfiguration
{
  public static function getAll()
  {
    $all = [];
    foreach (GeneralConfiguration::all() as $configure) {
      $all[$configure->name] = $configure->value;
    }
    return $all;
  }

  public static function setAll(array $inputs)
  {
    DB::transaction(function () use ($inputs) {
      foreach ($inputs as $name => $value) {
        $configure = GeneralConfiguration::where('name', $name)->first();
        $configure->value = $value;
        $configure->save();
      }
    });
  }

  public static function getMachineLicenseUnit()
  {
    return static::where('name', 'machine_license_unit')->first()->value;
  }
}
