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
      'user_id'   => ['integer'],
    ];
  }

  public function listByUser(Request $request, $id)
  {
    $request->merge(['user_id' => $id]);
    return self::list($request);
  }

  public function listByAccount(Request $request)
  {
    $request->merge(['user_id' => auth('api')->user()->id]);
    return self::list($request);
  }
}
