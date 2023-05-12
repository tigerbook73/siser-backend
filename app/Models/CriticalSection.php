<?php

namespace App\Models;

use App\Models\Base\CriticalSection as BaseCriticalSection;

class CriticalSection extends BaseCriticalSection
{
  public Subscription|User $object;

  static public function open(Subscription|User $object, string $action, string $first_step  = "", string $status = 'open'): self
  {
    $section = new self();
    $section->object = $object;

    if ($object instanceof Subscription) {
      $section->user_id = $object->user_id;
      $section->type = 'subscription';
      $section->object_id = $object->id;
      $section->action = [
        'time' => date('Y-m-d H:i:s'),
        'action' => $action,
        'status' => $object->status,
      ];
      $section->steps = $first_step ? [
        [
          'time' => date('Y-m-d H:i:s'),
          'step' => $first_step,
          'status' => $object->status,
        ]
      ] : [];
    } else if ($object instanceof User) {
      $section->user_id = $object->id;
      $section->type = 'user';
      $section->object_id = $object->id;
      $section->action = [
        'time' => date('Y-m-d H:i:s'),
        'action' => $action,
      ];
      $section->steps = $first_step ? [
        [
          'time' => date('Y-m-d H:i:s'),
          'step' => $first_step,
        ]
      ] : [];
    }

    $section->status = $status;
    $section->need_notify = ($status == 'open');
    $section->save();
    return $section;
  }


  static public function single(Subscription|User $object, string $action, string $first_step): self
  {
    return self::open($object, $action, $first_step, 'closed');
  }

  public function step(string $step): self
  {
    $steps = $this->steps;
    if ($this->object instanceof Subscription) {
      $steps[] = [
        'time' => date('Y-m-d H:i:s'),
        'step' => $step,
        'status' => $this->object->status,
      ];
    } else if ($this->object instanceof User) {
      $steps[] = [
        'time' => date('Y-m-d H:i:s'),
        'step' => $step,
      ];
    }
    $this->steps = $steps;
    $this->save();
    return $this;
  }

  public function close($step = null): void
  {
    $this->status = 'closed';
    $this->need_notify = false;

    if ($step) {
      $this->step($step);
    } else {
      $this->save();
    }
  }

  static public function unclosed()
  {
    return self::where('need_notify', true)
      ->where('status', 'open')
      ->where('updated_at', '<', now()->subMinutes(5))
      ->get();
  }
}
