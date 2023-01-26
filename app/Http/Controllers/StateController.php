<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class StateController extends SimpleController
{
  protected string $modelClass = State::class;


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    "US" => [
      ["name" => "Alabama", "short" =>  "AL"],
      ["name" => "Alaska", "short" =>  "AK"],
      ["name" => "Arizona", "short" =>  "AZ"],
      ["name" => "Arkansas", "short" =>  "AR"],
      ["name" => "California", "short" =>  "CA"],
      ["name" => "Colorado", "short" =>  "CO"],
      ["name" => "Connecticut", "short" =>  "CT"],
      ["name" => "Delaware", "short" =>  "DE"],
      ["name" => "District of Columbia", "short" =>  "DC"],
      ["name" => "Florida", "short" =>  "FL"],
      ["name" => "Georgia", "short" =>  "GA"],
      ["name" => "Hawaii", "short" =>  "HI"],
      ["name" => "Idaho", "short" =>  "ID"],
      ["name" => "Illinois", "short" =>  "IL"],
      ["name" => "Indiana", "short" =>  "IN"],
      ["name" => "Iowa", "short" =>  "IA"],
      ["name" => "Kansas", "short" =>  "KS"],
      ["name" => "Kentucky", "short" =>  "KY"],
      ["name" => "Louisiana", "short" =>  "LA"],
      ["name" => "Maine", "short" =>  "ME"],
      ["name" => "Maryland", "short" =>  "MD"],
      ["name" => "Massachusetts", "short" =>  "MA"],
      ["name" => "Michigan", "short" =>  "MI"],
      ["name" => "Minnesota", "short" =>  "MN"],
      ["name" => "Mississippi", "short" =>  "MS"],
      ["name" => "Missouri", "short" =>  "MO"],
      ["name" => "Montana", "short" =>  "MT"],
      ["name" => "Nebraska", "short" =>  "NE"],
      ["name" => "Nevada", "short" =>  "NV"],
      ["name" => "New Hampshire", "short" =>  "NH"],
      ["name" => "New Jersey", "short" =>  "NJ"],
      ["name" => "New Mexico", "short" =>  "NM"],
      ["name" => "New York", "short" =>  "NY"],
      ["name" => "North Carolina", "short" =>  "NC"],
      ["name" => "North Dakota", "short" =>  "ND"],
      ["name" => "Ohio", "short" =>  "OH"],
      ["name" => "Oklahoma", "short" =>  "OK"],
      ["name" => "Oregon", "short" =>  "OR"],
      ["name" => "Pennsylvania", "short" =>  "PA"],
      ["name" => "Puerto Rico", "short" =>  "PR"],
      ["name" => "Rhode Island", "short" =>  "RI"],
      ["name" => "South Carolina", "short" =>  "SC"],
      ["name" => "South Dakota", "short" =>  "SD"],
      ["name" => "Tennessee", "short" =>  "TN"],
      ["name" => "Texas", "short" =>  "TX"],
      ["name" => "Utah", "short" =>  "UT"],
      ["name" => "Vermont", "short" =>  "VT"],
      ["name" => "Virginia", "short" =>  "VA"],
      ["name" => "Virgin Islands", "short" =>  "VI"],
      ["name" => "Washington", "short" =>  "WA"],
      ["name" => "West Virginia", "short" =>  "WV"],
      ["name" => "Wisconsin", "short" =>  "WI"],
      ["name" => "Wyoming", "short" =>  "WY"],
    ],
    "AU" => [
      ["name" =>  "Australian Capital Territory", "short" => "ACT"],
      ["name" =>  "New South Wales", "short" => "NSW"],
      ["name" => "Northern Territory", "short" => "NT"],
      ["name" =>  "Queensland", "short" => "QLD"],
      ["name" => "South Australia", "short" => "SA"],
      ["name" =>  "Tasmania", "short" => "TAS"],
      ["name" =>  "Victoria", "short" => "VIC"],
      ["name" => "Western Australia", "short" => "WA"],
    ]
  ];

  public function list(Request $request)
  {
    if (!$request->country || !isset($this->mockData[$request->country])) {
      return response()->json(["message" => "Not Found"], 404);
    }

    return response()->json([
      "data" => $this->mockData[$request->country]
    ]);
  }
}
