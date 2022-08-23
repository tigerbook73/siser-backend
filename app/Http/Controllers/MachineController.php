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
      'serial_no' => ['filled'],
      'user_id'   => ['filled', 'integer'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      "serial_no" => ['required', 'max:255'],
      "user_id"   => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->whereNotNull('cognito_id'))],
      "model"     => ['required', 'max:255'],
      "nickname"  => ['max:255'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      "model"     => ['filled', 'string', 'max:255'],
      "nickname"  => ['max:255'],
    ];
  }

  public function listByUser(Request $request, $user_id)
  {
    $request->merge(['user_id' => $user_id]);
    return self::list($request);
  }

  public function listByAccount(Request $request)
  {
    $request->merge(['user_id' => auth('api')->user()->id]);
    return self::list($request);
  }
}
