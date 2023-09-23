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
      'siser_share_rate'            => ['filled', 'decimal:0,2', 'between:0,100'],
    ]);
    GeneralConfiguration::setAll($inputs);

    return $this->get();
  }
}
