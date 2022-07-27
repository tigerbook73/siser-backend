<?php

require_once __DIR__ . '/LDSAPI_common.php';

/**
 *  Purpose: - encode compactly the following information into a human typable
 *             validation code (x digits)
 * 
 *  When Sending the request, software needs to send:
 * 
 *   user_code          15-digit num
 *   device_id          16-digit num
 *   request_id          5-digit num
 * 
 *  Need to encode: (in the format for supplied sw_api_version, to allow extending 
 *  code to more digits in the future, and older, unupdated systems can still 
 *  read the results.
 * 
 *   - ResultCode (2 digits)
 *   - Sub Level (1 digit)
 *   - Cutter Count (1 digit)
 *   - Flags (2 digits) - reserved 2 digits for possible bitfield values, 32,16,8,4,2,1 = 6 possible flags, ??
 *   - (in the future, additional numbers that can be ignored by older systems)
 *   - Verification Hash (5 digits)   (all of the above + user_code + device_id + RequestID + SECRET_KEY)
 * 
 *   result is a 12 digit number. (should be displayed in 3 groupd of 4 digits, eg:  "1234 5678 9012"
 */
function generateVerificationCode(
  string $user_code,
  string $device_id,
  string $request_id,
  int $result_code = 0,
  int $sub_level = 0,
  int $cutter_number = 0,
  int $bitflags = 0
): ?string {
  if (!ctype_digit($user_code)  || strlen($user_code) > 15) return null;
  if (!ctype_digit($device_id)  || strlen($device_id) > 16) return null;
  if (!ctype_digit($request_id) || strlen($request_id) > 5) return null;
  if ($result_code < 0    || $result_code   > 99) return null;
  if ($sub_level < 0      || $sub_level     > 9)  return null;
  if ($cutter_number < 0  || $cutter_number > 9)  return null;
  if ($bitflags < 0       || $bitflags      > 99) return null;

  // normalize input data
  $user_code = normalize($user_code, 15);
  $device_id = normalize($device_id, 16);
  $request_id = normalize($request_id, 5);

  $result_code = normalize((string)$result_code, 2);
  $sub_level = normalize((string)$sub_level, 1);
  $cutter_number = normalize((string)$cutter_number, 1);
  $bitflags = normalize((string)$bitflags, 2);

  // mark as v1 reply
  $infostr = '1' . $result_code . $sub_level . $cutter_number . $bitflags;
  $rqstate = $user_code . $device_id . $request_id;
  $h = sha1($infostr . $rqstate . HASH_KEY);

  // get 5 digit hash
  $sa = strtoupper(substr($h, -4));  // to number    
  $h5 = base_convert($sa, 16, 10);
  $s5 = normalize($h5, 5);

  $result = $infostr . $s5;
  return $result;
}

function extractVerificationCode(
  string $user_code,
  string $device_id,
  string $request_id,
  string $verificationCode
): ?array {
  $result_code    = (int)substr($verificationCode, 1, 2);
  $sub_level      = (int)substr($verificationCode, 3, 1);
  $cutter_number  = (int)substr($verificationCode, 4, 1);
  $bitflags       = (int)substr($verificationCode, 5, 2);

  $tempCode = generateVerificationCode(
    $user_code,
    $device_id,
    $request_id,
    $result_code,
    $sub_level,
    $cutter_number,
    $bitflags
  );

  if ($tempCode !== $verificationCode) {
    return null;
  }

  return [
    'result_code' => $result_code,
    'subscription_level' => $sub_level,
    'cutter_number' => $cutter_number,
    'bitflags' => $bitflags,
  ];
}
