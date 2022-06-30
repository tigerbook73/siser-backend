<?php

namespace App\Http\Controllers;

use App\Models\GeneralConfiguration;
use Illuminate\Http\Request;

class GeneralConfigurationController extends SimpleController
{
  protected string $modelClass = GeneralConfiguration::class;

  const ID = 1;

  public function get()
  {
    return parent::index(self::ID);
  }

  public function set(Request $request)
  {
    return parent::update($request, self::ID);
  }
}
