<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
    return  redirect(config('siser.sign_in_uri') . '?' . http_build_query(['redirect' => config('siser.login_uri')]));
  }

  protected function getLogoutRedirect()
  {
    return redirect(config('siser.sign_out_uri') . '?' . http_build_query(['redirect' => url('/')]));
  }

  /**
   * Login
   */
  public function login(Request $request)
  {
    // if redirect query present, override the intended
    $redirect = $request->input('redirect');
    if ($redirect) {
      Cache::put('user_login_redirect', $redirect);
    }

    // domain-token
    $accessToken = $request->input('accessToken') ?? $request->cookie('siser')['sandbox']['accessToken'] ?? null;

    // check domain login
    if (!$accessToken) {
      return $this->getLoginRedirect();
    }

    // check accessToken validaty
    $client = new Provider($accessToken);
    $cognitoUser = $client->getCognitoUser();
    if (!$cognitoUser) {
      return $this->getLoginRedirect();
    }

    // create software user
    /** @var User|null $user */
    $user = User::where('name', $cognitoUser->username)->first();
    if (!$user) {
      $user = User::createFromCognitoUser($cognitoUser);;
    }

    $viewData = [
      'redirect' => Cache::pull('user_login_redirect'),
      'token' => json_encode([
        'access_token' => $this->jwtAuth()->login($user),
        'token_type' => 'bearer',
        'expires_in' => config('jwt.ttl') * 60,
      ]),
      'account' => json_encode($user->toResource('customer'))
    ];

    // login user
    return response()->view('user-login', $viewData);
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
   * @return \Illuminate\Http\JsonResponse
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
    return $user->toResource('customer');
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
