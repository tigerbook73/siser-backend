<?php

namespace App\Http\Controllers;

use App\Models\BillingInfo;
use Illuminate\Http\Request;

class BillingInfoController extends SimpleController
{
  protected string $modelClass = BillingInfo::class;


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    "first_name" => "User1",
    "last_name" => "Test",
    "phone" => "123345667",
    "organization" => null,
    "email" => "user1.test@iifuture.com",
    "address" => [
      "line1" => "123 Abc Street",
      "line2" => "",
      "city" => "New York",
      "postcode" => "55129",
      "state" => "NY",
      "country" => "US"
    ],
    "tax_id" => []
  ];

  public function get(Request $request)
  {
    return response()->json($this->mockData);
  }

  public function set(Request $request)
  {
    return response()->json($this->mockData);
  }
}
