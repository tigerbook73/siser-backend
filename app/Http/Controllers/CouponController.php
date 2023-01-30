<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends SimpleController
{
  protected string $modelClass = Coupon::class;


  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    [
      "id" => 1,
      "code" => "code20",
      "description" => "20% off for the first 2 months",
      "condition" => [
        "new_customer_only" => true,
        "new_subscription_only" => true,
        "upgrade_only" => true,
      ],
      "percentage_off" => 20,
      "period" => 2,
      "start_date" => "2023-01-01",
      "end_date" => "2023-06-30",
      "status" => "active",
    ],
    [
      "id" => 2,
      "code" => "code50",
      "description" => "50% off for the first month",
      "condition" => [
        "new_customer_only" => true,
        "new_subscription_only" => true,
        "upgrade_only" => true,
      ],
      "percentage_off" => 50,
      "period" => 1,
      "start_date" => "2023-01-01",
      "end_date" => "2023-06-30",
      "status" => "active",
    ]
  ];

  public function check(Request $request)
  {
    if (
      !$request->code ||
      !$request->plan_id ||
      !$request->country
    ) {
      return response()->json(["message" => "invalid ..."], 400);
    }

    foreach ($this->mockData as  $coupon) {
      if ($coupon["code"] == $request->code) {
        return response()->json($coupon);
      }
    }

    return response()->json(["message" => "Not found"], 404);
  }

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
      !$request->code ||
      !$request->condition ||
      !$request->start_date
    ) {
      return response()->json(['message' => 'invalid input'], 400);
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
    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['id'] == $id) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }

    $found['description'] = $request->description;
    $found['end_date'] = $request->end_date;

    return response()->json($found);
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
