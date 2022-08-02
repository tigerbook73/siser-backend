<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends SimpleController
{
  protected string $modelClass = Machine::class;

  protected function getListRules()
  {
    return [
      'serial_no' => [],
      'user_id'   => ['integer'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      "serial_no" => ['required',],
      "user_id"   => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->whereNotNull('cognito_id'))],
      "model"     => ['required',],
      "nickname"  => [],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      "model"     => [],
      "nickname"  => [],
    ];
  }

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
