<?php

namespace App\Models;

use App\Models\Base\LdsLog as BaseLdsLog;
use Illuminate\Support\Facades\Log;

class LdsLog
{
  public static function log(string $device_id, string $action, string $result, string $text)
  {
    // TODO: move to service and refactor
    Log::info('LDS_LOG: ', [
      'lds_instance_id' => $device_id,
      'action' => $action,
      'result' => $result,
      'text' => $text,
    ]);
  }
}
