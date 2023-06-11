<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\CognitoProvider;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends SimpleController
{
  protected string $modelClass = User::class;

  protected function getListRules(array $inputs = []): array
  {
    return [
      'name'                => ['filled'],
      'email'               => ['filled'],
      'full_name'           => ['filled'],
      'phone_number'        => ['filled'],
      'country_code'        => ['filled'],
      'subscription_level'  => ['filled'],
      'license_count'       => ['filled'],
    ];
  }

  public function create(Request $request)
  {
    $inputs = $request->validate([
      'create_from'     => ['required', 'in:access_token,username'],
      'access_token'    => ['required_if:create_from,access_token'],
      'username'        => ['required_if:create_from,username'],
    ]);

    /** @var CognitoProvider $cognitoProvider */
    $cognitoProvider = app()->make(CognitoProvider::class);
    if ($inputs['create_from'] == 'access_token') {
      $cognitoUser = $cognitoProvider->getCognitoUser($inputs['access_token']);
    } else {
      $cognitoUser = $cognitoProvider->getUserByName($inputs['username']);
    }

    if (!$cognitoUser) {
      return response()->json(['message' => 'user do not exist!'], 400);
    }

    $user = User::createOrUpdateFromCognitoUser($cognitoUser);
    return response()->json($user->toResource('admin'), $user->wasRecentlyCreated ? 201 : 200);
  }

  public function refresh($id)
  {
    /** @var User $user */
    $user = User::findOrFail($id);
    $cognitoProvider = app()->make(CognitoProvider::class);
    $cognitoUser = $cognitoProvider->getUserByName($user->name);
    $user->updateFromCognitoUser($cognitoUser);

    return response()->json($user->toResource('admin'));
  }

  public function updateDetails(Request $request, $id)
  {
    /** @var User $user */
    $user = User::findOrFail($id);

    $inputs = $request->validate([
      'type'     => ['required', Rule::in([User::TYPE_NORMAL, User::TYPE_VIP, User::TYPE_STAFF, User::TYPE_BLACKLISTED])],
    ]);

    $user->type = $inputs['type'];
    $user->save();
    return response()->json($user->toResource('admin'));
  }
}
