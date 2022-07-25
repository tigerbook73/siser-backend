<?php

namespace App\Models;

use App\Models\Base\LdsLog as BaseLdsLog;

class LdsLog extends BaseLdsLog
{

  public static function log(int $instance_id, string $action, string $result, string $text)
  {
    $log = new self([
      'lds_instance_id' => $instance_id,
      'action' => $action,
      'result' => $result,
      'text' => $text,
    ]);

    $log->save();
  }
}
