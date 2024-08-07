<?php

namespace App\Models;

use App\Models\Base\DrEventRawRecord as BaseDrEventRawRecord;

class DrEventRawRecord extends BaseDrEventRawRecord
{
  static public function createIfNotExist($event_id, $data): self
  {
    $record = self::firstOrCreate(
      ['event_id' => $event_id],
      ['data' => $data]
    );
    return $record;
  }
}
