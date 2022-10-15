<?php

namespace App\Http\Controllers;

use App\Services\Lds\LdsCoding;
use App\Services\Lds\LdsException;
use App\Services\Lds\LdsLicenseManager;
use Illuminate\Http\Request;

class LdsController extends Controller
{
  public function __construct(protected LdsCoding $ldsCoding, protected LdsLicenseManager $ldsManager)
  {
  }

  protected function isDigitString(string $str, int $length)
  {
    return is_string($str) && strlen($str) == $length && ctype_digit($str);
  }

  protected function validateCheckInputs(array $inputs): array
  {
    // validation
    if (empty($inputs['rq'])) {
      throw new LdsException(LdsException::LDS_ERR_BAD_REQUEST);
    }

    if (!$reqJson = $this->ldsCoding->decodeJsonText($inputs['rq'])) {
      throw new LdsException(LdsException::LDS_ERR_BAD_REQUEST);
    }

    if (!$reqData = (array)json_decode($reqJson)) {
      throw new LdsException(LdsException::LDS_ERR_BAD_REQUEST);
    }

    // more validation
    if (
      ($reqData['version'] ?? 0) != 1 ||
      !$this->isDigitString($reqData['request_id'] ?? "",  5)  ||
      !$this->isDigitString($reqData['device_id'] ?? "",  16)  ||
      !$this->isDigitString($reqData['user_code'] ?? "",  15)
    ) {
      throw new LdsException(LdsException::LDS_ERR_BAD_REQUEST);
    }

    return $reqData;
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
      if ($e->getCode() == LdsException::LDS_ERR_BAD_REQUEST[0]) {
        return response('Bad Request', 400);
      }

      if ($online) {
        return $this->prepareOnlineResponse(
          request_id: $reqData['request_id'],
          error_code: $e->getCode(),
          subscription_level: $e->subscription_level,
        );
      } else {
        return response()->view('lds-error-response', ['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()], 400, ['Cache-Control' => 'no-store']);
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
        return $this->prepareOfflineResponse(request_id: $reqData['request_id']);
      }
    } catch (LdsException $e) {
      // for bad request
      if ($e->getCode() == LdsException::LDS_ERR_BAD_REQUEST[0]) {
        return response('Bad Request', 400);
      }

      if ($online) {
        return $this->prepareOnlineResponse(
          request_id: $reqData['request_id'],
          error_code: $e->getCode(),
          subscription_level: $e->subscription_level,
        );
      } else {
        return response()->view('lds-error-response', ['errorCode' => $e->getCode(), 'errorMessage' => $e->getMessage()], 400, ['Cache-Control' => 'no-store']);
      }
    }
  }
}
