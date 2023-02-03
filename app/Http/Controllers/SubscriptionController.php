<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends SimpleController
{
  protected string $modelClass = Subscription::class;

  protected function getListRules()
  {
    return [
      'user_id'   => ['filled', 'integer'],
    ];
  }

  // public function listByUser(Request $request, $id)
  // {
  //   $request->merge(['user_id' => $id]);
  //   return self::list($request);
  // }

  // public function listByAccount(Request $request)
  // {
  //   $request->merge(['user_id' => auth('api')->user()->id]);
  //   return self::list($request);
  // }

  /**
   * TODO: MOCKUP
   */

  public $mockData = [
    [
      "id" => 1,
      "user_id" => 1,
      "coupon_id" => 1,
      "billing_info" => [],
      "plan_info" => [],
      "coupon_info" => [],
      "processing_fee_info" => [],
      "currency" => "USD",
      "price" => 10.0,
      "processing_fee" => 0.2,
      "tax" => 1.02,
      "current_period" => 1,
      "start_date" => "2023-01-24",
      "end_date" => "2023-01-24",
      "current_period_start_date" => "2023-01-24T23:36:26.305Z",
      "current_period_end_date" => "2023-01-24T23:36:26.305Z",
      "next_invoice_date" => "2021-07-02T13:15:10.0875833Z",
      "next_reminder_date" => "2023-01-24T23:36:26.305Z",
      "status" => "active",
      "sub_status" => "normal"
    ]
  ];

  public function initMockData()
  {
    foreach ($this->mockData as $index => $item) {
      $this->mockData[$index]['billing_info'] = (new BillingInfoController)->mockData;
      $this->mockData[$index]['plan'] = (new PlanController)->mockData[0];
      $this->mockData[$index]['coupon'] = (new CouponController)->mockData[0];
    }
  }

  public function listByAccount(Request $request)
  {
    $this->initMockData();

    return response()->json([
      "data" => $this->mockData[0]
    ]);
  }

  public function listByUser(Request $request, $id)
  {
    $this->initMockData();

    return response()->json([
      "data" => $this->mockData[0]
    ]);
  }

  public function create(Request $request)
  {
    if (!$request->plan_id || !$request->coupon_id) {
      return response()->json(['message' => 'invalid input'], 404);
    }

    $this->initMockData();

    $subscription = $this->mockData[0];
    $subscription['current_period'] = 0;
    $subscripiton["start_date"] = null;
    $subscripiton["end_date"] = null;
    $subscripiton["current_period_start_date"] = null;
    $subscripiton["current_period_end_date"] = null;
    $subscripiton["next_invoice_date"] = null;
    $subscripiton["next_reminder_date"] = null;
    $subscription["status"] = "draft";
    $subscription["sub_status"] = "normal";

    return response()->json($subscription);
  }

  public function index(int $id)
  {
    $this->initMockData();

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

  public function destroy(int $id)
  {
    $this->initMockData();

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

  public function pay(int $id)
  {

    $this->initMockData();

    $found = null;
    foreach ($this->mockData as $item) {
      if ($item['id'] == $id) {
        $found = $item;
      }
    }

    if (!$found) {
      return response()->json(null, 404);
    }

    $found['status'] = 'processing';
    return response()->json($found);
  }

  public function cancel(int $id)
  {

    $this->initMockData();

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
    $found['sub_status'] = 'cancelling';
    return response()->json($found);
  }
}
