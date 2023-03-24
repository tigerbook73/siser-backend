<?php

namespace App\Models;

use App\Models\Base\DrEvent as BaseDrEvent;

class DrEvent extends BaseDrEvent
{

  static public function log(array $eventInfo)
  {
    $event = new self();
    $event->id = $eventInfo['id'];
    $event->type = $eventInfo['type'];
    $event->save();
  }
}
