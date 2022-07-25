<?php

require __DIR__ . '/LDSAPI_UserID_Encode.php';
require __DIR__ . '/LDSAPI_Request_Encode.php';
require __DIR__ . '/LDSAPI_OfflineValidationCode.php';

/**
 * encodeing user id test
 */
function encodeUserIdTest()
{
  printf("%s() start.\n", __FUNCTION__);

  for ($i = 0; $i < 10; $i++) {
    $origin = random_int(1, 9999999);

    $encoded = encodeUserId($origin);
    $decoded = decodeUserId($encoded);

    // printf("%20s => %20s => %20s\n", $origin, $encoded, $decoded);

    if ($origin == $encoded || $origin != $decoded) {
      exit("error in " . __FUNCTION__);
    }
  }

  printf("%s() run successfully.\n", __FUNCTION__);
}

/**
 * encodeing verfication code test
 */
function generateVerficationCodeTest()
{
  printf("%s() start.\n", __FUNCTION__);

  for ($i = 0; $i < 10; $i++) {
    $user_code      = random_int(0, 999999999999999);
    $device_id      = random_int(0, 9999999999);
    $request_id     = random_int(0, 9999999999);
    $result_code    = random_int(0, 99);
    $sub_level      = random_int(0, 9);
    $cutter_number  = random_int(0, 9);
    $bitflags       = random_int(0, 99);

    $verification_code = generateVerificationCode(
      (string)$user_code,
      (string)$device_id,
      (string)$request_id,
      $result_code,
      $sub_level,
      $cutter_number,
      $bitflags
    );

    if (strlen($verification_code) != 12) {
      exit("error in " . __FUNCTION__);
    }
  }
  printf("%s() run successfully.\n", __FUNCTION__);
}

/**
 * helper
 */
function generateRandomString($length = 10)
{
  $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $charactersLength = strlen($characters);
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
    $randomString .= $characters[rand(0, $charactersLength - 1)];
  }
  return $randomString;
};

/**
 * encodeing JSON text
 */
function encodeJsonTextTest()
{
  printf("%s() start.\n", __FUNCTION__);

  for ($i = 0; $i < 10; $i++) {
    $origin = generateRandomString(random_int(20, 60));

    $encoded = encodeJsonText($origin);
    $decoded = decodeJsonText($encoded);

    // printf("origin : %s\n", $origin);
    // printf("decoded: %s\n", $decoded);
    // printf("encoded: %s\n", $encoded);

    if ($origin == $encoded || $origin != $decoded) {
      exit("error in " . __FUNCTION__);
    }
  }

  printf("%s() run successfully.\n", __FUNCTION__);
}

/**
 * format result test
 */
function formatResultTextTest()
{
  printf("%s() start.\n", __FUNCTION__);

  $origin = generateRandomString(120);
  $formatted = formatResultText($origin);

  printf("origin:\n%s\n", $origin);
  printf("formatted:\n%s\n", $formatted);
}



encodeUserIdTest();
generateVerficationCodeTest();
encodeJsonTextTest();
// formatResultTextTest();
