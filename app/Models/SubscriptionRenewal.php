<?php

namespace App\Models;

use App\Models\Base\SubscriptionRenewal as BaseSubscriptionRenewal;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRenewal extends BaseSubscriptionRenewal
{
  use TraitStatusTransition;

  /*
    ```mermaid
    stateDiagram-v2
    title: Subscription manually renewal state machine (e.g. Germany)
      [*] --> Pending                         : annual plan activated / extended
      Pending --> Active                      : ready to renew (1 month before next invoice date/reminder event)
      Pending --> [*]                         : subscription cancelled
      state Active 
      {
        [*] --> Ready
        Ready --> ReminderedFirst             : first reminder sent
        Ready --> ReminderedLast              : last reminder sent
        ReminderedFirst --> ReminderedLast    : last reminder sent
      }
      Active --> Completed                    : renew completed
      Active --> Cancelled                    : subscription cancelled
      Active --> Expired                      : renew expired / subscription failed / subsccription extended(old)
    ```
  */
  public const STATUS_PENDING                 = 'pending';
  public const STATUS_ACTIVE                  = 'active';
  public const STATUS_COMPLETED               = 'completed';
  public const STATUS_CANCELLED               = 'cancelled';
  public const STATUS_EXPIRED                 = 'expired';

  public const SUB_STATUS_NONE                = 'none';
  public const SUB_STATUS_READY               = 'ready';
  public const SUB_STATUS_FIRST_REMINDERED    = 'first_remindered';
  public const SUB_STATUS_FINAL_REMINDERED    = 'final_remindered';


  public function isPending()
  {
    return $this->status === static::STATUS_PENDING;
  }

  public function isActive()
  {
    return $this->status === static::STATUS_ACTIVE;
  }

  public function pending()
  {
    $this->setSubStatus(static::SUB_STATUS_NONE);
    $this->setStatus(static::STATUS_PENDING);
    return $this;
  }

  public function activate(): self
  {
    $this->setSubStatus(static::SUB_STATUS_READY);
    $this->setStatus(static::STATUS_ACTIVE);
    return $this;
  }

  public function cancel(): self
  {
    $this->setSubStatus(static::SUB_STATUS_NONE);
    $this->setStatus(static::STATUS_CANCELLED);
    return $this;
  }

  public function complete(): self
  {
    $this->setSubStatus(static::SUB_STATUS_NONE);
    $this->setStatus(static::STATUS_COMPLETED);
    return $this;
  }

  public function expire(): self
  {
    $this->setSubStatus(static::SUB_STATUS_NONE);
    $this->setStatus(static::STATUS_EXPIRED);
    return $this;
  }

  public function setSubStatus(string $subStatus): self
  {
    $this->sub_status = $subStatus;
    return $this;
  }

  /**
   * @return Collection<int, SubscriptionRenewal>
   */
  static public function findPending()
  {
    return static::where('status', static::STATUS_PENDING)
      ->where('start_at', '<=', now())
      ->where('expire_at', '>', now())
      ->get();
  }

  /**
   * @return Collection<int, SubscriptionRenewal>
   */
  static public function findToFirstReminder()
  {
    return static::where('status', static::STATUS_ACTIVE)
      ->where('sub_status', static::SUB_STATUS_READY)
      ->where('first_reminder_at', '<=', now())
      ->where('final_reminder_at', '>', now())
      ->get();
  }

  /**
   * @return Collection<int, SubscriptionRenewal>
   */
  static public function findToFinalReminder()
  {
    return static::where('status', static::STATUS_ACTIVE)
      ->whereNot('sub_status', static::SUB_STATUS_FINAL_REMINDERED)
      ->where('final_reminder_at', '<=', now())
      ->where('expire_at', '>', now())
      ->get();
  }

  /**
   * @return Collection<int, SubscriptionRenewal>
   */
  static public function findToExpire()
  {
    return static::where('status', static::STATUS_ACTIVE)
      ->where('expire_at', '<=', now())
      ->get();
  }

  public function info(): array
  {
    return [
      'id' => $this->id,
      'subscription_id' => $this->subscription_id,
      'period' => $this->period,
      'start_at' => $this->start_at->toISOString(),
      'expire_at' => $this->expire_at->toISOString(),
      'first_reminder_at' => $this->first_reminder_at->toISOString(),
      'final_reminder_at' => $this->final_reminder_at->toISOString(),
      'status' => $this->status,
      'sub_status' => $this->sub_status,
    ];
  }
}
