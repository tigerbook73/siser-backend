<?php

require '../app/Services/Lds/algorithm/LDSAPI_UserID_Encode.php';
require '../app/Services/Lds/algorithm/LDSAPI_Request_Encode.php';
require '../app/Services/Lds/algorithm/LDSAPI_OfflineValidationCode.php';


$rq           = $_GET["rq"] ?? null;
$data         = json_decode(decodeJsonText($rq));
echo "<p>rq</p>";
var_dump($rq);
echo "<p>decoded rq</p>";
var_dump($data);


echo "<p>fake response</p>";
$response = [
  'version'             => 1,
  'request_id'          => '98765',
  'error_code'          => 0,
  'result_code'         => 0,
  'subscription_level'  => 1,
  'cutter_number'       => 0,
  'bitflags'            => 0,
];
var_dump($response);

echo "<p>fake test</p>";
$output = formatResultText(encodeJsonText(json_encode($response)));
var_dump($output);
