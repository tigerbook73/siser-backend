<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Lds\LdsCoding;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class LdsController extends Controller
{
  public function __construct(protected LdsCoding $ldsCoding)
  {
  }

  protected function getCacheKey($user_code, $device_id)
  {
    return $user_code . '|' . $device_id;
  }

  protected function beautifyCode($user_code, $total_len, $seg_len)
  {
    $originCode = str_pad((string)$user_code, $total_len, "0", STR_PAD_LEFT);
    return implode(' ', str_split($originCode, $seg_len));
  }

  public function regDevice(Request $request)
  {
    // validation
    $inputs = $request->validate([
      'version' => ['required', Rule::in([1])],
      'device_id' => ['required', "digits:16"],
      'device_name' => ['required', 'string'],
      'online' => [Rule::in([0, 1])]
    ]);

    $result = $inputs;
    $result['online'] = $inputs['online'] ?? 1;

    /** @var User $user */
    $user = auth('api')->user();
    $result['user'] = $user->toResource('customer');
    $result['user_code'] = $this->ldsCoding->encodeUserId($user->id);

    return $result;
  }

  public function reg(Request $request)
  {
    // validation
    if (!$request->device_id) {
      return response("Bad request", 400);
    }

    $device_id = $request->device_id;
    $device_name = $request->device_name ?? $device_id;
    $online = (int)($request->online ?? 1);

    $result['title']  = 'Register new device';
    $result['op']     = 'reg';
    $result['online'] = $online;

    try {
      // user information
      $user_id = 1;
      $subscription_level = 1;
      $user_code = str_pad($this->ldsCoding->encodeUserId($user_id), 15, "0", STR_PAD_LEFT);

      $regRecord = [
        'user_id'             => $user_id,
        'user_code'           => $user_code,
        'device_id'           => $device_id,
        'subscription_level'  => $subscription_level,
        'device_name'         => $device_name,
      ];

      // save record
      Cache::put($this->getCacheKey($user_code, $device_id), json_encode($regRecord));

      /**
       * view data
       */
      // general data
      $result['success']          = true;
      $result['error_message']    = 'Register successfully';

      // reg data
      $result['reg_data']         = $regRecord;

      // display data
      $result['display_data']     = [
        'user_code'  => $this->beautifyCode($user_code, 15, 5),
        'online'           => $online,
        'redirect_url'     => $online ? "hostapp:?action=reg&&user_code=$user_code" : "",
      ];

      return response()->view("lds", $result, 200, ['Cache-Control' => 'no-store']);
    } catch (Exception $e) {
      $result['success'] = false;
      $result['error_message'] = $e->getMessage();
      return response()->view('lds', $result, 200, ['Cache-Control' => 'no-store']);
    }
  }

  public function checkIn(Request $request)
  {
    // validation
    if (!$request->rq) {
      return response("Bad request", 400);
    }

    $rq = $request->rq;
    $online = $request->online ?? 1;

    try {
      $reqData = (array)json_decode($this->ldsCoding->decodeJsonText($rq));

      // more validation
      if (
        $reqData['version'] != 1 ||
        !is_numeric($reqData['request_id']) ||
        !isset($reqData['device_id']) ||
        !isset($reqData['user_code'])
      ) {
        return response("Bad request", 400);
      }

      $regStore = Cache::get($reqData['user_code'] . '|' . $reqData['device_id']);
      if (!$regStore) {
        return response("Not registered yet", 400);
      }

      $regRecord = (array)json_decode($regStore);

      if ($online) {
        $result = [
          'version' => (int)$reqData['version'],
          'request_id' => $reqData['request_id'],
          'error_code' => 0,
          'result_code' => 0,
          'subscription_level' => $regRecord['subscription_level'],
          'cutter_number' => 0,
        ];
        return response(
          $this->ldsCoding->formatResultText($this->ldsCoding->encodeJsonText(json_encode($result))),
          200,
          ['Content-Type' => 'text/plain', 'Cache-Control' => 'no-store']
        );
      } else {
        $result['title']  = 'Check-in';
        $result['op']     = 'check-in';
        $result['online'] = $online;

        $result['reg_data']  = $regRecord;

        $result['display_data'] = [
          'request_id' => $reqData['request_id'],
          'result_code' => 0,
          'subscription_level' => $regRecord['subscription_level'],
          'cutter_number' => 0,
          'bitflags' => 0,
        ];
        $result['display_data']['verify_code'] = $this->ldsCoding->generateVerificationCode(
          $reqData['user_code'],
          $reqData['device_id'],
          $reqData['request_id'],
          $result['display_data']['result_code'],
          $result['display_data']['subscription_level'],
          $result['display_data']['cutter_number'],
          $result['display_data']['bitflags']
        );

        // for test
        $result['success'] = true;
        $result['error_message'] = "Check-in successfull";
        return response()->view('lds', $result, 200, ['Cache-Control' => 'no-store']);
      }
    } catch (Exception $e) {
      if ($online) {
        return response('', 400, ['Cache-Control' => 'no-store']);
      } else {
        $result['success'] = false;
        $result['error_message'] = $e->getMessage();
        return response()->view('lds', $result, 200, ['Cache-Control' => 'no-store']);
      }
    }
  }

  public function checkOut(Request $request)
  {
    // validation
    if (!$request->rq) {
      return response("Bad request", 400);
    }

    $rq = $request->rq;
    $online = $request->online ?? 1;

    try {
      $reqData = (array)json_decode($this->ldsCoding->decodeJsonText($rq));

      // more validation
      if (
        $reqData['version'] != 1 ||
        !is_numeric($reqData['request_id']) ||
        !isset($reqData['device_id']) ||
        !isset($reqData['user_code'])
      ) {
        return response("Bad request", 400);
      }

      $regStore = Cache::get($reqData['user_code'] . '|' . $reqData['device_id']);
      if (!$regStore) {
        return response("Not registered yet", 400);
      }

      $regRecord = (array)json_decode($regStore);

      if ($online) {
        $result = [
          'version' => (int)$reqData['version'],
          'request_id' => $reqData['request_id'],
          'error_code' => 0,
        ];
        return response(
          $this->ldsCoding->formatResultText($this->ldsCoding->encodeJsonText(json_encode($result))),
          200,
          ['Content-Type' => 'text/plain', 'Cache-Control' => 'no-store']
        );
      } else {
        $result['title']  = 'Check-out';
        $result['op']     = 'check-out';
        $result['online'] = $online;

        $result['reg_data']  = $regRecord;

        $result['display_data'] = [
          'request_id' => $reqData['request_id'],
          'result_code' => 0,
          'subscription_level' => 0,
          'cutter_number' => 0,
          'bitflags' => 0,
        ];
        $result['display_data']['verify_code'] = $this->ldsCoding->generateVerificationCode(
          $reqData['user_code'],
          $reqData['device_id'],
          $reqData['request_id'],
          $result['display_data']['result_code'],
          $result['display_data']['subscription_level'],
          $result['display_data']['cutter_number'],
          $result['display_data']['bitflags']
        );

        // for test
        $result['success'] = true;
        $result['error_message'] = "Check-out successfull";
        return response()->view('lds', $result, 200, ['Cache-Control' => 'no-store']);
      }
    } catch (Exception $e) {
      if ($online) {
        return response('', 400, ['Cache-Control' => 'no-store']);
      } else {
        $result['success'] = false;
        $result['error_message'] = $e->getMessage();
        return response()->view('lds', $result, 200, ['Cache-Control' => 'no-store']);
      }
    }
  }
}
