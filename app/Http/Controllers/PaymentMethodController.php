<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends SimpleController
{
  protected string $modelClass = PaymentMethod::class;


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    "id" => 1,
    "type" => "creditCard",
    "credit_card" => [
      "last_four_digits" => "3119",
      "brand" => "Visa"
    ],
    "provider_id" => "DR-source-id"
  ];

  public function get(Request $request)
  {
    return response()->json($this->mockData);
  }

  public function set(Request $request)
  {
    if (!$request->type || !$request->provider_id) {
      return response()->json(['message' => 'invalid input'], 404);
    }

    return response()->json($this->mockData);
  }
}
