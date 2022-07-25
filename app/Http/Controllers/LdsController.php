<?php

namespace App\Http\Controllers;

use App\Models\LdsRegistration;
use App\Models\User;
use App\Services\Lds\LdsCoding;
use App\Services\Lds\LdsException;
use App\Services\Lds\LdsLicenseManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LdsController extends Controller
{
  public function __construct(protected LdsCoding $ldsCoding, protected LdsLicenseManager $ldsManager)
  {
  }

  protected function isDigitString(string $str, int $length)
  {
    return is_string($str) && strlen($str) == $length && ctype_digit($str);
  }

  public function regDevice(Request $request)
  {
    // validation
    $inputs = $request->validate([
      'version' => [Rule::in([1])],
      'device_id' => ['required', "digits:16"],
      'device_name' => ['required', 'string'],
      'online' => [Rule::in([0, 1])]
    ]);

    /** @var User $user */
    $user = auth('api')->user();
    $user_code = $this->ldsCoding->encodeUserId($user->id);

    /** @var LdsRegistration $registration */
    $registration = LdsRegistration::where('user_code', $user_code)
      ->where('device_id', $inputs['device_id'])
      ->first() ?? new LdsRegistration();
    $registration->fill([
      'user_id' => $user->id,
      'device_id' => $inputs['device_id'],
      'user_code' => $user_code,
      'device_name' => $inputs['device_name'],
    ]);
    $registration->save();

    $result = $inputs;
    $result['online'] = $inputs['online'] ?? 1;
    $result['user'] = $user->toResource('customer');
    $result['user_code'] = $user_code;
    return $result;
  }

  protected function validateCheckInputs(array $inputs): array
  {
    // validation
    if (!$inputs['rq']) {
      throw new LdsException(LDS_ERR_BAD_REQUEST);
    }

    $rq = $inputs['rq'];
    if (!$input = $this->ldsCoding->decodeJsonText($rq)) {
      throw new LdsException(LDS_ERR_BAD_REQUEST);
    }

    if (!$reqData = (array)json_decode($input)) {
      throw new LdsException(LDS_ERR_BAD_REQUEST);
    }

    // more validation
    if (
      $reqData['version'] != 1 ||
      !$this->isDigitString($reqData['request_id'] ?? "",  5)  ||
      !$this->isDigitString($reqData['device_id'] ?? "",  16)  ||
      !$this->isDigitString($reqData['user_code'] ?? "",  15)
    ) {
      throw new LdsException(LDS_ERR_BAD_REQUEST);
    }

    return $reqData;
  }

  protected function prepareOnlineResponse(
    int $version             = 1,
    string $request_id       = "00000",
    int $error_code          = 0,
    int $result_code         = 0,
    int $subscription_level  = 0,
    int $cutter_number       = 0,
    int $bitflags            = 0,
  ) {
    $response = [
      'version'             => $version,
      'request_id'          => $request_id,
      'error_code'          => $error_code,
      'result_code'         => $result_code,
      'subscription_level'  => $subscription_level,
      'cutter_number'       => $cutter_number,
      'bitflags'            => $bitflags,
    ];
    return response(
      $this->ldsCoding->formatResultText($this->ldsCoding->encodeJsonText(json_encode($response))),
      200,
      ['Content-Type' => 'text/plain', 'Cache-Control' => 'no-store']
    );
  }

  protected function prepareOfflineResponse(
    int $version              = 1,
    string $request_id        = "00000",
    int $error_code           = 0,
    int $result_code          = 0,
    int $subscription_level   = 0,
    int $cutter_number        = 0,
    int $bitflags             = 0,
    string $verification_code = "000000000000",
    string $error_message     = ""
  ) {
    $response = [
      'version'             => $version,
      'request_id'          => $request_id,
      'error_code'          => $error_code,
      'result_code'         => $result_code,
      'subscription_level'  => $subscription_level,
      'cutter_number'       => $cutter_number,
      'bitflags'            => $bitflags,
      'verification_code'   => $verification_code,
      'error_message'       => $error_message,
    ];

    return response()->view('lds', $response, 200, ['Cache-Control' => 'no-store']);
  }

  /**
   * LDS check API
   * 
   * return status will always be 200
   */
  public function checkIn(Request $request)
  {
    $online = (int)(bool)($request->online ?? 1);

    try {
      $reqData = $this->validateCheckInputs($request->all());
      $result = $this->ldsManager->apply($reqData['user_code'], $reqData['device_id']);

      if ($online) {
        return $this->prepareOnlineResponse(
          request_id: $reqData['request_id'],
          subscription_level: $result->subscription_level,
          cutter_number: $result->cutter_number
        );
      } else {
        return $this->prepareOfflineResponse(
          request_id: $reqData['request_id'],
          subscription_level: $result->subscription_level,
          cutter_number: $result->cutter_number,
          verification_code: $this->ldsCoding->generateVerificationCode(
            $reqData['user_code'],
            $reqData['device_id'],
            $reqData['request_id'],
            0,
            $result->subscription_level,
            $result->cutter_number,
            0
          )
        );
      }
    } catch (LdsException $e) {
      // for bad request
      if ($e->getCode() == LDS_ERR_BAD_REQUEST[0]) {
        abort(400, 'Bad Request');
      }

      if ($online) {
        return $this->prepareOnlineResponse(
          request_id: $reqData['request_id'],
          error_code: $e->getCode(),
        );
      } else {
        abort(400, "Error: {$e->getCode()} : {$e->getMessage()}");
      }
    }
  }

  public function checkOut(Request $request)
  {
    $online = (int)(bool)($request->online ?? 1);

    try {
      $reqData = $this->validateCheckInputs($request->all());
      $this->ldsManager->release($reqData['user_code'], $reqData['device_id']);

      if ($online) {
        return $this->prepareOnlineResponse(request_id: $reqData['request_id']);
      } else {
        return $this->prepareOfflineResponse(
          request_id: $reqData['request_id']
        );
      }
    } catch (LdsException $e) {
      // for bad request
      if ($e->getCode() == LDS_ERR_BAD_REQUEST[0]) {
        abort(400, 'Bad Request');
      }

      if ($online) {
        return $this->prepareOnlineResponse(request_id: $reqData['request_id'], error_code: $e->getCode());
      } else {
        abort(400, "Error: {$e->getCode()} : {$e->getMessage()}");
      }
    }
  }
}
