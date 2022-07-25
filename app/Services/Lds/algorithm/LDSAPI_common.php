<?php

require_once __DIR__ . '/LDSAPI_config.php';


function normalize(string $origin, int $length, string $pad = '0')
{
  return substr(str_pad($origin, $length, $pad, STR_PAD_LEFT), 0, $length);
}
