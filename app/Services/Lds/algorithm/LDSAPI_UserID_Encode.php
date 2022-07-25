<?php

require_once __DIR__ . '/LDSAPI_common.php';


/**
 *  In: $s8 : the 7digit database ID, prepended with '1' (8 digits in total)
 *      $s5 : the 5 digit hash validation code
 * 
 *  Out: a 13 digit string, interleaving the two values (to avoid long sequences
 *      of repeated digits
 * 
 *  e.g.  s8 = 12345678, s5 = abcde, Result = a12b34c56d78e
 */
function InterleaveDigits(string $s8, string $s5): string
{
  $s8 = normalize($s8, 8);
  $s5 = normalize($s5, 5);

  $r = substr($s5, 0, 1) . substr($s8, 0, 2) .
    substr($s5, 1, 1) . substr($s8, 2, 2) .
    substr($s5, 2, 1) . substr($s8, 4, 2) .
    substr($s5, 3, 1) . substr($s8, 6, 2) .
    substr($s5, 4, 1);

  return $r;
}

/**
 *  Convert back to DatabaseID format (un shuffle)
 * 
 * e.g.  a12b34c56d78e --> 12345678abcde
 */
function UnInterleaveDigits(string $s13): string
{

  $s13 = normalize($s13, 13);

  $s5 = substr($s13, 0, 1) .
    substr($s13, 3, 1) .
    substr($s13, 6, 1) .
    substr($s13, 9, 1) .
    substr($s13, 12, 1);

  $s8 = substr($s13, 1, 2) .
    substr($s13, 4, 2) .
    substr($s13, 7, 2) .
    substr($s13, 10, 2);

  $r = $s8 . $s5;
  return $r;
}


/**
 * Take an (int) between 0 and 9,999,999 (10 million) and convert to a 
 * fifteen digit USER_ID the encoded USER_ID, 
 *
 * The resulting number has a 2 digit checksum to catch typing errors
 * and a 5 digit validation hash to prevent making up/impersonating a 
 * user_id.
 * the result is shuffled to eliminate repeating sequences of 0's
 */
function encodeUserId(int $dbid): ?string
{
  if ($dbid <= 0 || $dbid >= 1000_0000) return null;

  // 1. pad string to 7 digits. and append the fixed leading digit (reserved , =1)
  $s8 = '1' . normalize((string)$dbid, 7);

  // 2. calculate 5 digit sha1   
  $hash = sha1($s8 . HASH_KEY);   // use last 16 bits (8 hex chars) <= 5 digits      
  $sa = strtoupper(substr($hash, -4));  // to number    
  $h5 = base_convert($sa, 16, 10);
  $s5 = normalize($h5, 5);

  // 3. Shuffle (interleave) to remove repeated sequences, e.g.  100000000 (hard to type in correctly)
  $n = InterleaveDigits($s8, $s5);

  // 4. append mod97 check digits (2)   
  $n = (int)$n * 100;
  $result = ($n + 98) - $n % 97;
  $result = normalize((string)$result, 15);

  // verify check digit..
  $cs = $result % 97;  // <-- $cs must equal '1' to be valid!

  if ($cs == 1)  return $result;
  return null;
}


function ValidateChecksum15(string $s15): bool
{
  if (!strlen($s15)) return false;
  $cs = $s15 % 97;
  return ($cs == 1);
}

/**
 * Reverse transformation to validate and recover the original 7 digit
 * database ID.
 */
function decodeUserId(string $sUserID): ?int
{
  if (!strlen($sUserID)) return null;

  $s15 = normalize($sUserID, 15);
  if (!ValidateChecksum15($s15)) return null;

  //strip off 2 digit checksum
  $s13 = substr($s15, 0, 13);

  // unscramble
  $s = UnInterleaveDigits($s13);
  $s8 = substr($s, 0, 8);
  $s5 = substr($s, -5);

  //compute hash    
  $sh = sha1($s8 . HASH_KEY);   // use last 16 bits (8 hex chars) <= 5 digits
  $correcthash = strtoupper(substr($sh, -4));

  // supplied hash
  $h = $s5;
  $h5 = strtoupper(base_convert($h, 10, 16));
  $calculatedhash = normalize($h5, 4);

  // validate the hash values match
  if ($correcthash !== $calculatedhash) return null;

  // Hash OK, descramble
  $r = (int)substr($s8, 1, 7);
  return $r;
}
