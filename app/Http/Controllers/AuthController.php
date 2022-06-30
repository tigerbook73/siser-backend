<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
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
      redirect()->setIntendedUrl($redirect);
    }

    // already login
    if (auth('web')->check()) {
      return redirect()->intended('/'); // TODO: to sign-in home
    }

    // domain-token
    $accessToken = $request->cookie('siser')['sandbox']['accessToken'] ?? null;

    // check domain login
    if (!$accessToken) {
      return $this->getLoginRedirect();
    }

    $client = new Provider($accessToken);
    $cognitoUser = $client->getCognitoUser();
    if (!$cognitoUser) {
      return $this->getLoginRedirect();
    }

    // create software user
    $user = User::where('name', $cognitoUser->username)->first();
    if (!$user) {
      $user = User::updateOrCreate([
        'cognito_id' => $cognitoUser->id,
      ], [
        'name' => $cognitoUser->username,
        'email' => $cognitoUser->email,
        'password' => 'not allowed',
        // ...
      ]);
    }

    // login user
    auth('web')->login($user);
    $request->session()->regenerateToken();
    return redirect()->intended('/');
  }

  /**
   * logout
   */
  public function logout(Request $request)
  {
    if (auth('web')->check()) {
      auth('web')->logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
    }

    return $this->getLogoutRedirect();
  }

  public function me()
  {
    auth('web')->login(\App\Models\User::first()); // TODO: temp test only

    /** @var User $user */
    $user = auth('web')->user();
    return $user->toResource('customer');
  }
}
