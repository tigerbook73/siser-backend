<?php

namespace App\Http\Controllers;

use App\Models\LdsLicense;
use App\Models\User;
use App\Services\Lds\LdsCoding;
use App\Services\Lds\LdsException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LdsLicenseController extends Controller
{
  public function __construct(protected LdsCoding $ldsCoding)
  {
  }

  public function accountGet()
  {
    /** @var User $user */
    $user = auth('api')->user();
    $ldsLicense = LdsLicense::fromUserIdAndRefresh($user->id);
    return $ldsLicense->toResource('customer');
  }

  public function userGet($id)
  {
    /** @var User $user */
    $user = User::findOrFail($id);
    $ldsLicense = LdsLicense::fromUserIdAndRefresh($user->id);
    return $ldsLicense->toResource('admin');
  }

  public function regDevice(Request $request)
  {
    // validation
    $inputs = $request->validate([
      'version'     => ['required', Rule::in([1])],
      'device_id'   => ['required', 'digits:16'],
      'device_name' => ['required', 'string', 'max:255'],
    ]);

    try {
      /** @var User $user */
      $user = auth('api')->user();
      $ldsLicense = LdsLicense::fromUserId($user->id);

      $inputs['user_code'] = $this->ldsCoding->encodeUserId($user->id);
      $ldsLicense->registerDevice($inputs, $request->ip());

      $result = $inputs;
      $result['user'] = $user->toResource('customer');
      return response()->json($result);
    } catch (LdsException $e) {
      return response()->json(['message' => $e->getMessage(), 'code' => $e->getCode()], 400);
    }
  }

  public function unregDevice(Request $request)
  {
    // validation
    $inputs = $request->validate([
      'version'     => ['required', Rule::in([1])],
      'device_id'   => ['required', 'digits:16'],
      'user_code'   => ['required', 'digits:15'],
    ]);

    try {
      /** @var User $user */
      $user = auth('api')->user();
      $ldsLicense = LdsLicense::fromUserId($user->id);
      $ldsLicense->unregisterDevice($inputs['device_id'], $request->ip());
    } catch (LdsException $e) {
      return response()->json(['message' => $e->getMessage(), 'code' => $e->getCode()], 400);
    }
  }

  protected function isDigitString(string $str, int $length): bool
  {
    return is_string($str) && strlen($str) == $length && ctype_digit($str);
  }

  /**
   * @return array LDS check in/out decoded-request
   * @throws BadRequestHttpException
   */
  protected function validateCheckInputs(array $inputs): array
  {
    // validation
    if (empty($inputs['rq'])) {
      throw new BadRequestHttpException();
    }

    if (!$reqJson = $this->ldsCoding->decodeJsonText($inputs['rq'])) {
      throw new BadRequestHttpException();
    }

    if (!$reqData = (array)json_decode($reqJson)) {
      throw new BadRequestHttpException();
    }

    // more validation
    if (
      ($reqData['version'] ?? 0) != 1 ||
      !$this->isDigitString($reqData['request_id'] ?? '',  5)  ||
      !$this->isDigitString($reqData['device_id'] ?? '',  16)  ||
      !$this->isDigitString($reqData['user_code'] ?? '',  15)
    ) {
      throw new BadRequestHttpException();
    }
    return $reqData;
  }

  /**
   * @return int $user_id
   * @throws LdsException
   */
  protected function validateUserCode(string $user_code): int
  {
    if (!$userId = $this->ldsCoding->decodeUserId($user_code)) {
      throw new LdsException(LdsException::LDS_ERR_INVALID_USER_CODE);
    };
    return $userId;
  }

  protected function prepareOnlineResponse(
    string $request_id,
    int $version             = 1,
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
    string $request_id,
    int $version              = 1,
    int $error_code           = 0,
    int $result_code          = 0,
    int $subscription_level   = 0,
    int $cutter_number        = 0,
    int $bitflags             = 0,
    string $verification_code = '000000000000',
    string $error_message     = ''
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
      $userId = $this->validateUserCode($reqData['user_code']);
      $ldsLicense = LdsLicense::fromUserId($userId);
      $result = $ldsLicense->checkInDevice($reqData['device_id'], $request->ip());

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
      if ($online) {
        return $this->prepareOnlineResponse(
          request_id: $reqData['request_id'] ?? '0',
          error_code: $e->getCode(),
          subscription_level: $e->data['subscription_level'] ?? 0,
        );
      } else {
        return response()->view(
          'lds-error-response',
          ['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()],
          400,
          ['Cache-Control' => 'no-store']
        );
      }
    }
  }

  public function checkOut(Request $request)
  {
    $online = (int)(bool)($request->online ?? 1);

    try {
      $reqData = $this->validateCheckInputs($request->all());
      $userId = $this->validateUserCode($reqData['user_code']);
      $ldsLicense = LdsLicense::fromUserId($userId);
      $result = $ldsLicense->checkOutDevice($reqData['device_id'], $request->ip());

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
          cutter_number: $result->cutter_number
        );
      }
    } catch (LdsException $e) {
      if ($online) {
        return $this->prepareOnlineResponse(
          request_id: $reqData['request_id'] ?? '0',
          error_code: $e->getCode(),
          subscription_level: $e->data['subscription_level'] ?? 0,
        );
      } else {
        return response()->view(
          'lds-error-response',
          ['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()],
          400,
          ['Cache-Control' => 'no-store']
        );
      }
    }
  }
}
