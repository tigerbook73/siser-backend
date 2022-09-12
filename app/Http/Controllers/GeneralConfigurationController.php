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
      'machine_license_unit' => ['filled', 'integer', 'between:1,10'],
    ]);
    GeneralConfiguration::setAll($inputs);

    // TODO: update all user's license_count
    // ..

    return $this->get();
  }
}
