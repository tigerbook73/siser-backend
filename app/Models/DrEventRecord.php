<?php

namespace App\Models;

use App\Models\Base\DrEventRecord as BaseDrEventRecord;
use App\Services\DigitalRiver\SubscriptionManagerResult;


class DrEventRecord extends BaseDrEventRecord
{
  use TraitStatusTransition;

  const STATUS_INIT         = 'init';
  const STATUS_PROCESSING   = 'processing';
  const STATUS_COMPLETED    = 'completed';
  const STATUS_FAILED       = 'failed';


  static public function fromDrEventId(string $event_id): ?self
  {
    return self::where('event_id', $event_id)->first();
  }

  static public function fromDrEventIdOrNew(string $event_id, string $type): self
  {
    $event = self::fromDrEventId($event_id) ?? new self([
      'event_id'    => $event_id,
      'type'        => $type,
      'status'      => self::STATUS_INIT,
    ]);

    if ($event->type !== $type) {
      throw new \Exception('event type is not matched', 500);
    }

    return $event;
  }

  public function isInit(): bool
  {
    return $this->status === self::STATUS_INIT;
  }

  public function isCompleted(): bool
  {
    return $this->status === self::STATUS_COMPLETED;
  }

  public function isFailed(): bool
  {
    return $this->status === self::STATUS_FAILED;
  }

  public function isProcessing(): bool
  {
    return $this->status === self::STATUS_PROCESSING;
  }

  public function startProcessing(): self
  {
    if (!$this->isInit() && !$this->isFailed()) {
      throw new \Exception('event is not init or failed', 500);
    }

    $this->setStatus(self::STATUS_PROCESSING);
    $this->save();
    return $this;
  }

  public function complete(SubscriptionManagerResult $result): void
  {
    $this->user_id          = $result->getUserId();
    $this->subscription_id  = $result->getSubscriptionId();
    $this->data             = $result->getData();
    $this->messages         = $result->getMessages();

    $this->setStatus(self::STATUS_COMPLETED);
    $this->save();
  }

  public function fail(SubscriptionManagerResult $result): void
  {
    $this->user_id          = $result->getUserId();
    $this->subscription_id  = $result->getSubscriptionId();
    $this->data             = $result->getData();
    $this->messages         = $result->getMessages();

    $this->setStatus(self::STATUS_FAILED);
    $this->save();
  }
}
