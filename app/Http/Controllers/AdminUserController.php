<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends SimpleController
{
  protected string $modelClass = AdminUser::class;

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
    if ($request->password) {
      $request->merge(['password' => Hash::make($request->password)]);
    }
    return parent::update($request, $id);
  }
}
