<?php

namespace App\Services\Lds;

use Exception;

require __DIR__ . '/LDSAPI_Request_Encode.php';
require __DIR__ . '/LDSAPI_UserID_Encode.php';
require __DIR__ . '/LDSAPI_OfflineValidationCode.php';


class LdsCoding
{
  public function encodeUserId(int $userId): ?string
  {
    try {
      return encodeUserId($userId);
    } catch (Exception) {
      return null;
    }
  }

  public function decodeUserId(string $userCode): ?int
  {
    try {
      return decodeUserId($userCode);
    } catch (Exception) {
      return null;
    }
  }

  public function encodeJsonText(string $jsonText): ?string
  {
    try {
      return encodeJsonText($jsonText);
    } catch (Exception) {
      return null;
    }
  }

  public function decodeJsonText(string $text): ?string
  {
    try {
      return decodeJsonText($text);
    } catch (Exception) {
      return null;
    }
  }

  public function formatResultText(string $text): ?string
  {
    try {
      return formatResultText($text);
    } catch (Exception) {
      return null;
    }
  }

  public function generateVerificationCode(
    string $user_code,
    string $device_id,
    string $request_id,
    int $result_code,
    int $sub_level,
    int $cutter_count,
    int $bitflags
  ): ?string {
    try {
      return generateVerificationCode(
        $user_code,
        $device_id,
        $request_id,
        $result_code,
        $sub_level,
        $cutter_count,
        $bitflags
      );
    } catch (Exception) {
      return null;
    }
  }
}
