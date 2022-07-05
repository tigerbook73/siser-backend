<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Services\Cognito\Provider;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
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

    if (auth('admin')->attempt($credentials, $request->input('remember'))) {
      $request->session()->regenerate();
      return $this->me();
    }

    return response()->json(['message' => 'invalid credentials', 400]);
  }

  public function logout(Request $request)
  {
    auth('admin')->logout();
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
    /** @var AdminUser $adminUser */
    $adminUser = auth('admin')->user();

    $input = $request->all();
    $validator = Validator::make($input, [
      'current_password' => ['required', 'string'],
      'password' => ['required', RulesPassword::min(8)],
    ])->after(function ($validator) use ($adminUser, $input) {
      if (!isset($input['current_password']) || !Hash::check($input['current_password'], $adminUser->password)) {
        $validator->errors()->add('current_password', __('The provided password does not match your current password.'));
      }
    });

    $validator->validate();

    $adminUser->password = Hash::make($input['password']);
    $adminUser->save();

    return response('', 204);
  }

  public function token()
  {
    // TODO: JWT token
    return [
      'token' => "xxxxxxxxxxxx",
      'expires_in' => 1231312311
    ];
  }

  public function me()
  {
    auth('admin')->login(\App\Models\AdminUser::first()); // TODO: temp test only

    /** @var AdminUser $user */
    $user = auth('admin')->user();

    return $user->toResource('admin');
  }
}
