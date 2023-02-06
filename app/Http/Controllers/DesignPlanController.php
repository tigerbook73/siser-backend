<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DesignPlanController extends SimpleController
{
  protected string $modelClass = Plan::class;


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
      "price_list" => [
        [
          "country" => "US",
          "currency" => "USD",
          "price" => 0,
        ]
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
      "price_list" => [
        [
          "country" => "US",
          "currency" => "USD",
          "price" => 9.9,
        ],
        [
          "country" => "AU",
          "currency" => "AUD",
          "price" => 15.2,
        ]
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

  public function create(Request $request)
  {
    if (
      !$request->name ||
      !$request->catagory ||
      !$request->subscription_level ||
      !$request->price_list
    ) {
      return response()->json(['message' => 'invalid input'], 404);
    }

    return response()->json($this->mockData[1]);
  }

  public function destroy(int $id)
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
  }

  public function update(Request $request, int $id)
  {
    if (
      !$request->name ||
      !$request->catagory ||
      !$request->subscription_level ||
      !$request->price_list
    ) {
      return response()->json(['message' => 'invalid input'], 404);
    }

    return response()->json($this->mockData[1]);
  }

  public function activate(Request $request, int $id)
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

    $found['status'] = 'active';
    return response()->json($found);
  }

  public function deactivate(Request $request, int $id)
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

    $found['status'] = 'inactive';
    return response()->json($found);
  }

  public function history(Request $request, int $id)
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

    return response()->json([
      'data' => [
        "id" => 1,
        "design_plan" => $found,
        "created_at" => "2023-03-01",
      ]
    ]);
  }
}
