<?php

use Paddle\SDK\Environment;

return [
  /**
   * valid value: test, staging, prod
   */
  'api_key'           => env('PADDLE_API_KEY'),
  'environment'       => env('PADDLE_ENV', Environment::SANDBOX),

  'webhook_id'        => env('PADDLE_WEBHOOK_ID'),
  'webhook_secret'    => env('PADDLE_WEBHOOK_SECRET'),
];
