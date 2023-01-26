<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends SimpleController
{
  protected string $modelClass = Country::class;


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    [
      "name" => "Australia",
      "code" => "AU"
    ],
    [
      "name" => "The United State of America",
      "code" => "US"
    ],
  ];

  public function list(Request $request)
  {
    return response()->json([
      "data" => $this->mockData
    ]);
  }
}
