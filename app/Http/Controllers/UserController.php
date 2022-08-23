<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\CognitoProvider;
use Illuminate\Http\Request;

class UserController extends SimpleController
{
  protected string $modelClass = User::class;


  protected function getListRules()
  {
    return [
      'name'      => ['filled'],
      'email'     => ['filled'],
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

    $user = User::createFromCognitoUser($cognitoUser);
    return response()->json($user->toResource('admin'), 201);
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

  protected function full(int $id)
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

    return  response()->json($result);
  }

  protected function fullByAccount()
  {
    return $this->full(auth('api')->user()->id);
  }
}
