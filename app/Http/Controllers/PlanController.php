<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends SimpleController
{
  protected string $modelClass = Plan::class;

  protected function getListRules()
  {
    return [
      'name'        => ['filled'],
      'catagory'    => ['filled', 'in:machine,software'],
      'status'      => ['filled', 'in:active,inactive'],
    ];
  }

  public function deactivate(Request $request, $id)
  {
    $this->validateUser();

    /** @var Plan $plan */
    $plan = $this->customizeQuery($this->baseQuery(), [])->findOrFail($id);

    // validate status
    if ($plan->status !== 'active') {
      abort(400, 'Can not be deactivated');
    }


    $plan->deactivate();
    return $this->transformSingleResource($plan);
  }


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    [
      "id" => 1,
      "name" => "LDS Basic Plan",
      "catagory" => "machine",
      "description" => "basic plan",
      "subscription_level" => 1,
      "url" => "",
      "status" => "active",
      "price" => [
        "country" => "US",
        "currency" => "USD",
        "price" => 0,
      ]
    ],
    [
      "id" => 2,
      "name" => "LDS Pro Plan",
      "catagory" => "machine",
      "description" => "pro plan",
      "subscription_level" => 2,
      "url" => "",
      "status" => "active",
      "price" => [
        "country" => "US",
        "currency" => "USD",
        "price" => 10,
      ]
    ]
  ];

  public function list(Request $request)
  {
    return response()->json([
      "data" => $this->mockData
    ]);
  }

  public function index(int $id)
  {
    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['id'] == $id) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }
    return response()->json($found);
  }
}
