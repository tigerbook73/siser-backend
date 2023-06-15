<?php

namespace App\Services\Lds;

use Exception;

class LdsException extends Exception
{
  const LDS_ERR_OK                        = [0, 'OK'];
  const LDS_ERR_UNKNOWN                   = [1, 'Unknown error'];
  const LDS_ERR_BAD_REQUEST               = [2, 'Bad request'];
  // Notice: the above 3 exception shall not be used

  const LDS_ERR_TOO_MANY_DEVICES          = [3, 'Too many devices'];
  const LDS_ERR_INVALID_USER_CODE         = [4, 'Invalid user code'];
  const LDS_ERR_USER_NOT_REGISTERED       = [5, 'User not registered'];
  const LDS_ERR_DEVICE_NOT_REGISTERED     = [6, 'Device not registered'];
  const LDS_ERR_DEVICE_NOT_CHECK_IN       = [7, 'Device not check-in yet'];
  const LDS_ERR_USER_DOESNT_HAVE_LICENSE  = [8, 'User doesn\'t have any licenses'];

  public function __construct(array $error, public array $data = [])
  {
    parent::__construct($error[1], $error[0]);
  }
}
