<?php

namespace App\Models;

use App\Models\Base\LdsLog as BaseLdsLog;
use Illuminate\Support\Facades\Log;

class LdsLog extends BaseLdsLog
{

  public static function log(int $instance_id, string $action, string $result, string $text)
  {
    // TODO: refactor
    Log::info('LDS_LOG: ', [
      'lds_instance_id' => $instance_id,
      'action' => $action,
      'result' => $result,
      'text' => $text,
    ]);
  }
}
