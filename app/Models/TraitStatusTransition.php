<?php

namespace App\Models;

use Carbon\Carbon;

trait TraitStatusTransition
{
  public function setStatus(string $status, Carbon $time = null)
  {
    $this->status = $status;

    $status_transitions = $this->status_transitions ?? [];
    $status_transitions[$status] = $time ?? now();
    $this->status_transitions = $status_transitions;

    return $this;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function getStatusTimestamp(string $status): Carbon|null
  {
    if (isset($this->status_transitions[$status])) {
      return Carbon::parse($this->status_transitions[$status]);
    }
    return null;
  }
}
