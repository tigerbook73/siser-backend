<?php

namespace App\Models;

use App\Models\Base\DrEvent as BaseDrEvent;

class DrEvent extends BaseDrEvent
{

  static public function exists(string $event_id): bool
  {
    return self::where('event_id', $event_id)->count() > 0;
  }

  static public function log(array $eventInfo)
  {
    $event = new self();
    $event->event_id        = $eventInfo['id'];
    $event->type            = $eventInfo['type'];
    $event->subscription_id = $eventInfo['subscription_id'] ?? null;
    $event->save();
  }
}
