<?php

namespace App\Models;

use App\Models\Base\DrEventRecord as BaseDrEventRecord;

class DrEventRecord extends BaseDrEventRecord
{
  use TraitStatusTransition;

  const STATUS_COMPLETED    = 'completed';
  const STATUS_FAILED       = 'failed';
  const STATUS_PROCESSING   = 'processing';

  const ACTION_DO           = 'do';
  const ACTION_IGNORE       = 'ignore';
  const ACTION_ERROR        = 'error';

  public string $action = self::ACTION_DO;
  public string|null $error = null;

  static public function fromDrEventId(string $event_id): ?self
  {
    return self::where('event_id', $event_id)->first();
  }

  /**
   * start processing dr event
   */
  static public function startProcessing(string $event_id, string $type): DrEventRecord
  {
    $event = self::fromDrEventId($event_id);
    if ($event) {
      switch ($event->status) {
        case self::STATUS_COMPLETED:
          $event->action  = self::ACTION_IGNORE;
          $event->error   = 'duplicated';
          return $event;

        case self::STATUS_PROCESSING:
          $event->action = self::ACTION_ERROR;
          $event->error = 'in-processing';
          return $event;

        case self::STATUS_FAILED:
          $event->setStatus(self::STATUS_PROCESSING);
          $event->save();
          return $event;
      }
      throw new \Exception('event status is in unknown', 500);
    }

    $event = new self([
      'event_id'  => $event_id,
      'type'      => $type,
      'status'    => self::STATUS_PROCESSING,
    ]);
    $event->setStatus(self::STATUS_PROCESSING);
    $event->save();
    return $event;
  }

  public function complete(int|null $subscriptionId): void
  {
    $this->subscription_id = $subscriptionId;
    $this->setStatus(self::STATUS_COMPLETED);
    $this->save();
  }

  public function fail(): void
  {
    $this->setStatus(self::STATUS_FAILED);
    $this->save();
  }
}
