<?php

namespace App\Http\Controllers;

use App\Models\GeneralConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralConfigurationController extends Controller
{
  protected string $modelClass = GeneralConfiguration::class;

  const ID = 1;

  public function get()
  {
    $generalConfigure = [];
    foreach (GeneralConfiguration::all() as $configure) {
      $generalConfigure[$configure->name] = $configure->value;
    }
    return $generalConfigure;
  }

  public function set(Request $request)
  {
    // TODO: validation
    $input = $request->all();

    DB::transaction(function () use ($input) {
      foreach ($input as $name => $value) {
        $configure = GeneralConfiguration::where('name', $name)->first();
        $configure->value = $value;
        $configure->save();
      }
    });

    return $this->get();
  }
}
