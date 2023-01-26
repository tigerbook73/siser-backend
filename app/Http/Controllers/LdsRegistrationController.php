<?php

namespace App\Http\Controllers;

use App\Models\LdsRegistration;
use App\Models\User;
use App\Services\Lds\LdsCoding;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LdsRegistrationController extends Controller
{
  public function __construct(protected LdsCoding $ldsCoding)
  {
  }

  public function regDevice(Request $request)
  {
    // validation
    $inputs = $request->validate([
      'version'     => ['required', Rule::in([1])],
      'device_id'   => ['required', "digits:16"],
      'device_name' => ['required', 'string', 'max:255'],
    ]);

    /** @var User $user */
    $user = auth('api')->user();
    $user_code = $this->ldsCoding->encodeUserId($user->id);

    /** @var LdsRegistration $registration, inactive is allowed */
    $registration = LdsRegistration::where('user_code', $user_code)
      ->where('device_id', $inputs['device_id'])
      ->first() ?? new LdsRegistration();
    $registration->fill([
      'user_id'     => $user->id,
      'device_id'   => $inputs['device_id'],
      'user_code'   => $user_code,
      'device_name' => $inputs['device_name'],
      'status'      => 'active',
    ]);
    $registration->save();

    $result = $inputs;
    $result['user'] = $user->toResource('customer');
    $result['user_code'] = $user_code;
    return response()->json($result);
  }

  public function unregDevice(Request $request)
  {
    // validation
    $inputs = $request->validate([
      'version'     => ['required', Rule::in([1])],
      'device_id'   => ['required', "digits:16"],
      'user_code'   => ['required', "digits:15"],
    ]);

    /** @var User $user */
    $user = auth('api')->user();

    /** @var LdsRegistration|null $registration */
    $registration = LdsRegistration::where('user_id', $user->id)
      ->where('user_code', $inputs['user_code'])
      ->where('device_id', $inputs['device_id'])
      ->where('status', 'active')
      ->first();
    if (!$registration) {
      return response()->json(['message' => 'Device not registered!'], 404);
    }

    $registration->status = 'inactive';
    $registration->save();
  }
}
