<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as RulesPassword;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AdminAuthController extends Controller
{
  private function jwtAuth(): JWTGuard
  {
    /** @var JWTGuard */
    $guard = auth('admin');
    return $guard;
  }

  public function login(Request $request)
  {
    // input validation
    $credentials = $request->validate([
      'email' => ['required', 'email'],
      'password' => ['required'],
    ]);

    // if env === local && email === password
    if (app()->environment('local') && $credentials['email'] === $credentials['password']) {
      return $this->loginTest($request);
    }

    if (!$token = $this->jwtAuth()->attempt($credentials)) {
      return response()->json(['message' => 'invalid credentials'], 400);
    }

    return $this->respondWithToken($token);
  }

  public function loginTest(Request $request)
  {
    if (!app()->environment('local')) {
      return response()->json(['message' => 'not allowed'], 400);
    }

    $user = AdminUser::where('email', $request->input('email'))->first();
    if (!$user) {
      return response()->json(['message' => 'admin user not found'], 400);
    }
    $token = $this->jwtAuth()->login($user);
    return $this->respondWithToken($token);
  }

  public function logout()
  {
    $this->jwtAuth()->logout();
    return response('', 204);
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
    $inputs = $request->validate([
      'token' => ['required'],
      'email' => ['required', 'email'],
      'password' => ['required', RulesPassword::min(8)->mixedCase()->numbers()->symbols()],
    ]);
    $inputs['cognito_id'] = null;

    $status = Password::reset(
      $inputs,
      function (AdminUser $user, string $password) {
        $user->password = Hash::make($password);
        $user->save();

        event(new PasswordReset($user));
      }
    );

    return $status === Password::PASSWORD_RESET
      ? response('', 204)
      : response()->json(['message' => __($status)], 400);
  }

  public function updatePassword(Request $request)
  {
    /** @var AdminUser $adminUser */
    $adminUser = $this->jwtAuth()->user();

    $inputs = $request->all();
    $validator = Validator::make($inputs, [
      'current_password' => ['required', 'string'],
      'password' => ['required', RulesPassword::min(8)->mixedCase()->numbers()->symbols()],
    ])->after(function ($validator) use ($adminUser, $inputs) {
      if (!Hash::check($inputs['current_password'], $adminUser->password)) {
        $validator->errors()->add('current_password', __('The provided password does not match your current password.'));
      }
    });
    $validator->validate();

    $adminUser->password = Hash::make($inputs['password']);
    $adminUser->save();

    return response('', 204);
  }

  public function refresh()
  {
    return $this->respondWithToken($this->jwtAuth()->refresh());
  }

  public function me()
  {
    /** @var AdminUser $user */
    $user = $this->jwtAuth()->user();
    return  response()->json($user->toResource('admin'));
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
