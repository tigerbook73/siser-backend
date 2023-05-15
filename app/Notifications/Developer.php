<?php

namespace App\Notifications;

use Illuminate\Notifications\Notifiable;

class Developer
{
  use Notifiable;

  public string $email;

  public function __construct()
  {
    $this->email = config('mail.developer');
  }

  public function getKey()
  {
    return 1;
  }
}
