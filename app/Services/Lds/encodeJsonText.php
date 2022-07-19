<?php

require __DIR__ . '/LDSAPI_UserID_Encode.php';
require __DIR__ . '/LDSAPI_Request_Encode.php';
require __DIR__ . '/LDSAPI_OfflineValidationCode.php';

$origin = '
{
  "version": 1,
  "request_id": "12345",
  "device_id": "0000111122223333",
  "user_code": "410800600201008"
}
';

$compressed = json_encode(json_decode($origin));
$encoded = encodeJsonText($compressed);

printf("Origin:       %s\n", $origin);
printf("Encoded:      %s\n", $compressed);
printf("Compressed:   %s\n", $encoded);
