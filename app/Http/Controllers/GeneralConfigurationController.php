<?php

namespace App\Http\Controllers;

use App\Models\GeneralConfiguration;
use Illuminate\Http\Request;

class GeneralConfigurationController extends Controller
{
  protected string $modelClass = GeneralConfiguration::class;


  public function get()
  {
    return response()->json(GeneralConfiguration::getAll());
  }

  public function set(Request $request)
  {
    $inputs = $request->validate([
      'machine_license_unit'        => ['filled', 'integer', 'between:1,10'],
      'plan_reminder_offset_days'   => ['filled', 'integer', 'between:1,30'],
      'plan_billing_offset_days'    => ['filled', 'integer', 'between:0,30', 'lte:plan_collection_period_days'],
      'plan_collection_period_days' => ['filled', 'integer', 'between:0,30'],
      'siser_share_rate'            => ['filled', 'decimal:0,2', 'between:0,100'],
    ]);
    GeneralConfiguration::setAll($inputs);

    return $this->get();
  }
}
