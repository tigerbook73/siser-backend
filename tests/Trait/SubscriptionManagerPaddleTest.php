<?php

namespace Tests\Trait;

use App\Services\Paddle\SubscriptionManagerPaddle;
use Tests\Helper\SubscriptionManagerPaddleMockup;

trait SubscriptionManagerPaddleTest
{
  public function setupSubscriptionManagerPaddleTest()
  {
    app()->bind(SubscriptionManagerPaddle::class, SubscriptionManagerPaddleMockup::class);
  }
}
