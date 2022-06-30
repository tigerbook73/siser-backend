<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends SimpleController
{
  protected string $modelClass = Subscription::class;

  public function listByUser(Request $request, $id)
  {
    $request->merge(['user_id' => $id]);
    return self::list($request);
  }

  public function listByLoginUser(Request $request)
  {
    auth('web')->login(\App\Models\User::first()); // TODO: temp test only

    $request->merge(['user_id' => auth('web')->user()->id]);
    return self::list($request);
  }
}
