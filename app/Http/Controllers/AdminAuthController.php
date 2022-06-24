<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Cognito\Provider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RulesPassword;

class AdminAuthController extends Controller
{
  public function login(Request $request)
  {
    // input validation
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    // admin users
    $credentials['cognito_id'] = null;

    if (Auth::attempt($credentials, $request->input('remember'))) {
      $request->session()->regenerate();

      return $this->me();
    }

    return response()->json(['message' => 'invalid credentials', 400]);
  }

  public function logout(Request $request)
  {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'logout successfully']);
  }

  public function forgotPassword(Request $request)
  {
    // input validation
    $inputs = $request->validate([
      'email' => ['required', 'email'],
    ]);

    // admin users
    $inputs['cognito_id'] = null;

    $status = Password::sendResetLink($inputs);

    return ($status === Password::RESET_LINK_SENT)
      ? response('', 204)
      : response()->json(['message' => __($status)], 400);
  }

  public function resetPassword(Request $request)
  {
    $input = $request->validate([
      'token' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', RulesPassword::min(8)],
    ]);
    $input['cognito_id'] = null;

    $status = Password::reset(
      $input,
      function ($user, $password) {
        $user->forceFill([
          'password' => Hash::make($password)
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
      }
    );

    return $status === Password::PASSWORD_RESET
      ? response('', 204)
      : response()->json(['message' => __($status)]);
  }

  public function updatePassword(Request $request)
  {
    /** @var User $user */
    $user = Auth::user();
    if ($user->cognito_id !== null) {
      abort(401);
    }

    $input = $request->all();
    $validator = Validator::make($input, [
      'current_password' => ['required', 'string'],
      'password' => ['required', RulesPassword::min(8)],
    ])->after(function ($validator) use ($user, $input) {
      if (!isset($input['current_password']) || !Hash::check($input['current_password'], $user->password)) {
        $validator->errors()->add('current_password', __('The provided password does not match your current password.'));
      }
    });

    $validator->validate();

    $user->password = Hash::make($input['password']);
    $user->save();

    return response('', 204);
  }

  /**
   * Login
   */
  public function login1(Request $request)
  {
    // if redirect query present, override the intended
    $redirect = $request->input('redirect');
    if ($redirect) {
      redirect()->setIntendedUrl($redirect);
    }

    // already login
    if (Auth::check()) {
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
    Auth::login($user);
    $request->session()->regenerateToken();
    return redirect()->intended('/');
  }

  /**
   * logout
   */
  public function logout1(Request $request)
  {
    if (Auth::check()) {
      Auth::guard('web')->logout();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
    }

    return $this->getLogoutRedirect();
  }

  public function me()
  {
    $user = Auth::user();

    return $user ? [
      'username' => $user->name,
      'name' => $user->name,
      'email' => $user->email,
      'roles' => ['customer'],
    ] : null;
  }
}
