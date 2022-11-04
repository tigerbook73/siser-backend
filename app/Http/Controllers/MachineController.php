<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

  public function create(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      "serial_no" => ['required', 'max:255'],
      "user_id"   => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->whereNotNull('cognito_id'))],
      "model"     => ['required', 'max:255'],
      "nickname"  => ['max:255'],
    ]);

    /** @var Machine|null $machine */
    $machine = Machine::where([
      'serial_no' => $inputs['serial_no']
    ])->first();

    if ($machine) {
      // must be same user
      if ($machine->user_id != $inputs['user_id']) {
        $message = __('validation._machine', ['attribute' => 'serial_no']);
        return response()->json(['message' => $message, 'errors' => ['serial_no' => [$message]]], 422);
      }

      // update
      $machine->fill($inputs);
      $machine->save();
      return  response()->json($this->transformSingleResource($machine), 200);
    } else {
      // create
      $machine = new Machine($inputs);
      DB::transaction(
        fn () => $machine->save()
      );
      return  response()->json($this->transformSingleResource($machine), 201);
    }
  }

  public function transfer(Request $request, int $id)
  {
    // input validation
    $inputs = $request->validate([
      "new_user_id"   => [
        'required',
        // user exist and not owns any machine
        Rule::exists('users', 'id')->where(fn ($q) => $q->whereNotNull('cognito_id'))
      ],
    ]);

    /** @var Machine|null $machine */
    $machine = Machine::findOrFail($id);
    $machine->transfer($inputs['new_user_id']);

    return $machine->unsetRelations()->toResource('admin');
  }
}
