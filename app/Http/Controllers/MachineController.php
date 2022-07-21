<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MachineController extends SimpleController
{
  protected string $modelClass = Machine::class;

  public function listByUser(Request $request, $user_id)
  {
    $request->merge(['user_id' => $user_id]);
    return self::list($request);
  }

  public function listByLoginUser(Request $request)
  {
    $request->merge(['user_id' => auth('api')->user()->id]);
    return self::list($request);
  }
}
