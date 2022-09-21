<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\CognitoProvider;
use App\Services\Cognito\CognitoUser;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
  private function jwtAuth(): JWTGuard
  {
    /** @var JWTGuard */
    $guard = auth('api');
    return $guard;
  }

  protected function getLoginRedirect()
  {
    return  redirect(
      config('siser.sign_in_uri') . '?' . http_build_query([
        'redirect' => url(config('siser.login_uri'), [], app()->environment('production') ?: null)
      ]),
      302,
      ['Cache-Control' => 'no-store']
    );
  }

  protected function getLogoutRedirect()
  {
    return redirect(
      config('siser.sign_out_uri') . '?' . http_build_query([
        'redirect' => url(config('siser.login_uri'), [], app()->environment('production') ?: null)
      ]),
      302,
      ['Cache-Control' => 'no-store']
    );
  }

  /**
   * Login from siser website
   */
  public function loginWeb(Request $request)
  {
    // domain-token
    $accessToken = $request->input('accessToken') ?? null;

    // check domain login
    if (!$accessToken) {
      return $this->getLoginRedirect();
    }

    /** @var CognitoUser|null $cognitoUser */
    $cognitoUser = app()->make(CognitoProvider::class)->getCognitoUser($accessToken);
    if (!$cognitoUser) {
      abort(400, 'access token is invalid');
    }

    // create / update software user
    /** @var User|null $user */
    $user = User::where('name', $cognitoUser->username)->first();
    if (!$user) {
      $user = User::createFromCognitoUser($cognitoUser);
    } else {
      $user->updateFromCognitoUser($cognitoUser);
    }

    $viewData = [
      'token' => json_encode([
        'access_token' => $this->jwtAuth()->login($user),
        'token_type' => 'bearer',
        'expires_in' => config('jwt.ttl') * 60,
      ]),
      'account' => json_encode($user->toResource('customer'))
    ];

    // login user
    return response()->view('user-login', $viewData, 200, ['Cache-Control' => 'no-store']);
  }

  /**
   * logout from siser website
   */
  public function logoutWeb()
  {
    return $this->getLogoutRedirect();
  }

  public function loginTest(Request $request)
  {
    $input = $request->only(['email']);

    $user = User::where('email', $input['email'])->first();
    $token  = $this->jwtAuth()->tokenById($user->id);

    return $this->respondWithToken($token);
  }

  /**
   * Log the user out (Invalidate the token).
   *
   * @return \Illuminate\Http\Response
   */
  public function logout()
  {
    $this->jwtAuth()->logout();
    return response('', 204);
  }

  /**
   * Get the authenticated User.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function me()
  {
    /** @var User $user */
    $user = $this->jwtAuth()->user();
    return response()->json($user->toResource('customer'));
  }

  /**
   * Refresh a token.
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function refresh()
  {
    return $this->respondWithToken($this->jwtAuth()->refresh());
  }

  /**
   * Get the token array structure.
   *
   * @param  string $token
   *
   * @return \Illuminate\Http\JsonResponse
   */
  protected function respondWithToken($token)
  {
    return response()->json([
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => config('jwt.ttl') * 60
    ]);
  }
}
