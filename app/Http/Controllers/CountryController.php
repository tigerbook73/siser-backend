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
      "code" => "AU",
      "currency" => "AUD",
      "processing_fee_rate" => 2.0,
      "explicit_processing_fee" => true,
    ],
    [
      "name" => "The United State of America",
      "code" => "US",
      "currency" => "USD",
      "processing_fee_rate" => 2.0,
      "explicit_processing_fee" => true,
    ],
  ];

  public function list(Request $request)
  {
    return response()->json([
      "data" => $this->mockData
    ]);
  }
}
