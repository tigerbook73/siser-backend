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

  public function indexWithCode(string $code)
  {
    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['code'] == $code) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }
    return response()->json($found);
  }

  public function create(Request $request)
  {
    if (
      !$request->code ||
      !$request->name ||
      !$request->currency ||
      !$request->processing_fee_rate ||
      !$request->explicit_processing_fee
    ) {
      return response()->json(['message' => 'invalid input'], 400);
    }

    return response()->json($this->mockData[1]);
  }

  public function updateWithCode(Request $request, string $code)
  {
    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['code'] == $code) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }

    $found['name'] = $request->name;
    $found['currency'] = $request->currency;
    $found['processing_fee_rate'] = $request->processing_fee_rate;
    $found['explicit_processing_fee'] = $request->explicit_processing_fee;

    return response()->json($found);
  }

  public function destroyWithCode(string $code)
  {
    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['code'] == $code) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }
  }
}
