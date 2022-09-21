<?php

namespace App\Services\Lds;

use Exception;

define('LDS_ERR_OK',                        [0, 'OK']);
define('LDS_ERR_UNKNOWN',                   [1, 'Unknown error']);
define('LDS_ERR_BAD_REQUEST',               [2, 'Bad request']);
define('LDS_ERR_TOO_MANY_DEVICES',          [3, 'Too many devices']);
define('LDS_ERR_INVALID_USER_CODE',         [4, 'Invalid user code']);
define('LDS_ERR_USER_NOT_REGISTERED',       [5, 'User not registered']);
define('LDS_ERR_DEVICE_NOT_REGISTERED',     [6, 'Device not registered']);
define('LDS_ERR_DEVICE_NOT_CHECK_IN',       [7, 'Device not check-in yet']);
define('LDS_ERR_USER_DOESNT_HAVE_LICENSE',  [8, 'User doesn\'t have any licenses']);


class LdsException extends Exception
{
  public function __construct(array $error)
  {
    parent::__construct($error[1], $error[0]);
  }
}
