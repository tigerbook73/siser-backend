<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends SimpleController
{
  protected string $modelClass = AdminUser::class;

  protected function getCreateRules()
  {
    return [
      'name'      => ['required', 'string', 'unique:' . AdminUser::class],
      'email'     => ['required', 'email', 'unique:' . AdminUser::class],
      'full_name' => ['required', 'string'],
      'roles'     => ['required', 'array'], // TODO: more validation
      'password'  => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'full_name' => ['string'],
      'roles'     => ['array'],
      'password'  => [Password::min(8)->mixedCase()->numbers()->symbols()],
    ];
  }

  protected function getDeleteRules()
  {
    return [];
  }

  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);

    $adminUser = new AdminUser($inputs);
    $adminUser->password = Hash::make($inputs['password']);
    DB::transaction(
      fn () => $adminUser->save()
    );
    return  response()->json($this->transformSingleResource($adminUser), 201);
  }

  public function update(Request $request, int $id)
  {

    $this->validateUser();
    $inputs = $this->validateUpdate($request, $id);
    if (empty($inputs)) {
      abort(400, 'input data can not be empty.');
    }

    $object = $this->baseQuery()->findOrFail($id);

    // validate and update attributers
    $updatable = $this->modelClass::getUpdatable($this->userType);
    foreach ($inputs as $attr => $value) {
      if (!in_array($attr, $updatable)) {
        abort(400, 'attribute: [' . $attr . '] is not updatable.');
      }
      $object->$attr = $value;
    }
    if (isset($inputs['password'])) {
      $object->password = Hash::make($inputs['password']);
    }

    DB::transaction(
      fn () => $object->save()
    );
    return response()->json($this->transformSingleResource($object->unsetRelations()));
  }
}
