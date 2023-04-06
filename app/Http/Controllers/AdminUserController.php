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

  protected function getListRules()
  {
    return [
      'name'                => ['filled'],
      'email'               => ['filled'],
      'full_name'           => ['filled'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'name'      => ['required', 'string', 'max:255', 'unique:' . AdminUser::class],
      'email'     => ['required', 'email', 'max:255', 'unique:' . AdminUser::class],
      'full_name' => ['required', 'string', 'max:255'],
      'roles'     => ['required', 'array', 'min:1'],
      'roles.*'   => ['required', 'string', 'distinct', 'in:admin,siser-backend,support'],
      'password'  => ['required', 'string', 'max:32', Password::min(8)->mixedCase()->numbers()->symbols()],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'full_name' => ['filled', 'string', 'max:255'],
      'roles'     => ['filled', 'array', 'min:1'],
      'roles.*'   => ['filled', 'string', 'distinct', 'in:admin,siser-backend,support'],
      'password'  => ['filled', 'string', 'max:32', Password::min(8)->mixedCase()->numbers()->symbols()],
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

    $adminUser = $this->baseQuery()->findOrFail($id);
    $adminUser->forceFill($inputs);
    if (isset($inputs['password'])) {
      $adminUser->password = Hash::make($inputs['password']);
    }

    DB::transaction(
      fn () => $adminUser->save()
    );
    return response()->json($this->transformSingleResource($adminUser->unsetRelations()));
  }
}
