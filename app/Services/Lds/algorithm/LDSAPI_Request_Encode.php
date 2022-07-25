<?php

require_once __DIR__ . '/LDSAPI_common.php';


/**
 * Encode/Decode a string by xor'ing with stream of random bytes.
 * Does not alter the current seed upon return.
 */
function EncodeStr(string $s, int $seed): string
{
  for ($i = 0; $i < strlen($s); $i++) {
    $w = (GetRand16($seed) & 255);
    $s[$i] = chr(ord($s[$i]) ^ $w);
  }
  return $s;
}

function encodeJsonText(string $s): string
{
  $seed = time() % 65537;
  $s = EncodeStr($s, $seed);
  $s = strtoupper(bin2hex($s));
  if (strlen($s) % 2) $s = '0' . $s;
  $s1 = strtoupper(base_convert((string)$seed, 10, 16));
  while (strlen($s1) < 4) $s1 = '0' . $s1;
  $s1 = $s1 . $s;
  $sh = sha1($s1 . HASH_KEY);
  $sh = strtoupper(substr($sh, -8));
  $s1 = $s1 . $sh;
  return $s1;
}

function formatResultText(string $s1): ?string
{
  if (!strlen($s1)) return null;

  $cr = chr(13) . chr(10);
  $s2 = "";
  for ($i = 0; $i < strlen($s1); $i = $i + 48) {
    $s = substr($s1, $i, 48);
    $s2 = $s2 . $s . $cr;
  }

  $s2 = '<!-- BeginLDSData' . $cr . $s2 . '~EndLDSData -->';
  return $s2;
}

/**
 * Format:  Hex Str (uppercase),    
 *
 * rseed   data       hash[4]  (rseed.data.HASH_KEY)
 * [0123][4......n-8][8   ]
 */
function decodeJsonText(string $s): ?string
{
  if (strlen($s) < 12) return null;

  $s_seed = substr($s, 0, 4);
  $s_hash = substr($s, -8);
  $s_data = substr($s, 4, -8);
  $s_hsrc = strtoupper(substr($s, 0, -8)) . HASH_KEY;
  // validate correct hash
  $sa = sha1($s_hsrc);
  $sa = strtoupper(substr($sa, -8));
  if ($sa <> $s_hash) return null;

  // hash is valid...
  $s_seed = (int)base_convert($s_seed, 16, 10);
  $s = _hex2bin($s_data);
  $s = EncodeStr($s, $s_seed);
  return $s;
}

function _hex2bin(string $h): string
{
  $r = '';
  if (strlen($h) % 2 == 1)
    $h = '0' . $h;

  for ($a = 0; $a < strlen($h); $a += 2) {
    $r .= chr(hexdec($h[$a] . $h[$a + 1]));
  }
  return $r;
}

/**
 * need to have a RandSeed value somewhere to store ...
 */
function GetRand16(int &$r): int
{
  if ($r < 1) $r = 1;
  $r = (75 * $r) % 65537;
  return $r;
}
