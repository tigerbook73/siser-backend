<?php

namespace App\Models;

use App\Models\Base\GeneralConfiguration as BaseGeneralConfiguration;
use Illuminate\Support\Facades\DB;

/**
 * GeneralConfiguration
 */
class Configuration
{
  /** @var int $machine_license_unit This valud defines how many license units one machine will give.*/
  public $machine_license_unit = 0;

  /** @var int $plan_reminder_offset_days The number of days before open an invoice (start to charge customer) that we need to send a remider to customer.*/
  public $plan_reminder_offset_days = 0;

  /** @var int $plan_billing_offset_days The number of days before the end of the billing period that Digital River opens an invoice.*/
  public $plan_billing_offset_days = 0;

  /** @var int $plan_collection_period_days The number of days that Digital River attempts to collect payment.*/
  public $plan_collection_period_days = 0;

  /** @var int $siser_share_rate 0-1,000,000 subscription*/
  public $siser_share_rate = 0;
}

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
      $oldConfiguration = static::getConfiguration();

      foreach ($inputs as $name => $value) {
        $configure = GeneralConfiguration::where('name', $name)->first();
        $configure->value = $value;
        $configure->save();
      }

      // TODO: move this to queue tasks
      $newConfiguration = static::getConfiguration();

      // update license count 
      if ($oldConfiguration->machine_license_unit != $newConfiguration->machine_license_unit) {
        /** @var User[] $users */
        $users = User::has('machines')->withCount('machines')->get(); // @phpstan-ignore-line
        foreach ($users as $user) {
          $user->license_count = $user->machines_count * $newConfiguration->machine_license_unit; // @phpstan-ignore-line
          $user->save();
        }
      }

      // TODO: update billing reminder / offset / collection period
      if ($oldConfiguration->plan_reminder_offset_days != $newConfiguration->plan_reminder_offset_days) {
        // do something?
      }
      if ($oldConfiguration->plan_billing_offset_days != $newConfiguration->plan_billing_offset_days) {
        // do something?
      }
      if ($oldConfiguration->plan_collection_period_days != $newConfiguration->plan_collection_period_days) {
        // do something?
      }

      // TODO: update siser share rate
      if ($oldConfiguration->siser_share_rate != $newConfiguration->siser_share_rate) {
        // do something?
      }
    });
  }

  public static function getMachineLicenseUnit()
  {
    return static::where('name', 'machine_license_unit')->first()->value;
  }

  public static function getConfiguration(): Configuration
  {
    $configuration = new Configuration;
    foreach (static::getAll() as $key => $value) {
      $configuration->$key = $value;
    }
    return $configuration;
  }
}
