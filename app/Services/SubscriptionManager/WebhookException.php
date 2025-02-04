<?php

namespace App\Services\SubscriptionManager;

use Exception;

class WebhookException extends Exception
{
  public function __construct(string $message, int $code = 599, \Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
  }
}
