<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\CognitoProvider;
use Illuminate\Http\Request;

class UserController extends SimpleController
{
  protected string $modelClass = User::class;

  public function create(Request $request)
  {
    $create_from = $request->create_from;
    $access_token = $request->access_token;
    $username = $request->username;

    /** @var CognitoProvider $cognitoProvider */
    $cognitoProvider = app()->make(CognitoProvider::class);
    if ($create_from == 'access_token') {
      $cognitoUser = $cognitoProvider->getCognitoUser($access_token);
    } else {
      $cognitoUser = $cognitoProvider->getUserByName($username);
    }

    if (!$cognitoUser) {
      return response()->json(['message' => 'user do not exist!'], 400);
    }

    $user = User::createFromCognitoUser($cognitoUser);

    return response()->json($user->toResource('admin'), 201);
  }

  public function refresh(Request $request, $id)
  {
    /** @var User $user */
    $user = User::find($id);
    $cognitoProvider = app()->make(CognitoProvider::class);
    $cognitoUser = $cognitoProvider->getUserByName($user->name);
    $user->updateFromCognitoUser($cognitoUser);

    return $user->toResource('admin');
  }

  protected function fullByUserId(int $id)
  {
    $this->validateUser();

    /** @var User $user */
    $user = $this->customizeQuery($this->baseQuery(), [])->findOrFail($id);
    $subscriptions = $user->subscriptions()->with('plan')->get();
    $machines = $user->machines;

    $result = $this->transformSingleResource($user);
    $result['subscriptions'] = array_map(function ($subscription) {
      return $subscription->toResource($this->userType);
    }, $subscriptions->all());
    $result['machines'] = array_map(function ($machine) {
      return $machine->toResource($this->userType);
    }, $machines->all());

    return $result;
  }

  protected function fullByLoginUser(Request $request)
  {
    return $this->fullByUserId(auth('api')->user()->id);
  }

  protected function fullByUser(Request $request, $id)
  {
    return $this->fullByUserId($id);
  }
}
