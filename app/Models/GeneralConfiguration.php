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
      $machine_license_unit = static::getMachineLicenseUnit();

      foreach ($inputs as $name => $value) {
        $configure = GeneralConfiguration::where('name', $name)->first();
        $configure->value = $value;
        $configure->save();
      }

      // TODO: move this to queue tasks
      // update license count for all users for 
      $new_machine_license_unit = static::getMachineLicenseUnit();
      if ($machine_license_unit != $new_machine_license_unit) {
        /** @var User[] $users */
        $users = User::has('machines')->withCount('machines')->get();
        foreach ($users as $user) {
          $user->license_count = $user->machines_count * $new_machine_license_unit;
          $user->save();
        }
      }
    });
  }

  public static function getMachineLicenseUnit()
  {
    return static::where('name', 'machine_license_unit')->first()->value;
  }
}
