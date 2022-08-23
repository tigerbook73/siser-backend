<?php

require '../app/Services/Lds/algorithm/LDSAPI_UserID_Encode.php';
require '../app/Services/Lds/algorithm/LDSAPI_Request_Encode.php';
require '../app/Services/Lds/algorithm/LDSAPI_OfflineValidationCode.php';

function printTitle($line)
{
  printf("<p><b>%s</b></p>\n", $line);
};

function printLine($line)
{
  printf("<p>%s</p>\n", $line);
};

function main(string $rq)
{
  printTitle("");
  printTitle("RQ:");
  printLine("$rq");

  $request = json_decode(decodeJsonText($rq));
  printTitle("");
  printTitle("RQ decoded:");
  printLine("[version]: {$request->version}");
  printLine("[request_id]: {$request->request_id}");
  printLine("[device_id]: {$request->device_id}");
  printLine("[user_code]: {$request->user_code}");

  $response = [
    'version'             => 1,
    'request_id'          => $request->request_id,
    'error_code'          => 0,
    'result_code'         => 0,
    'subscription_level'  => 1,
    'cutter_number'       => 0,
    'bitflags'            => 0,
  ];
  printTitle("");
  printTitle("Response (before encoding):");
  printLine("[version]: {$response['version']}");
  printLine("[request_id]: {$response['request_id']}");
  printLine("[error_code]: {$response['error_code']}");
  printLine("[result_code]: {$response['result_code']}");
  printLine("[subscription_level]: {$response['subscription_level']}");
  printLine("[cutter_number]: {$response['cutter_number']}");
  printLine("[bitflags]: {$response['bitflags']}");

  $responseText = formatResultText(encodeJsonText(json_encode($response)));
  printTitle("");
  printTitle("Response result:");
  foreach (explode("\r\n", $responseText) as $line) {
    printLine(htmlspecialchars($line));
  }
}

$rq = $_GET["rq"] ?? null;
main($rq);
