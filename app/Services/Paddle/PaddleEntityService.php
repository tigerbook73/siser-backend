<?php

namespace App\Services\Paddle;

use App\Services\SubscriptionManager\SubscriptionManagerResult;
use App\Services\LicenseSharing\LicenseSharingService;

class PaddleEntityService
{
  public SubscriptionManagerPaddle $manager;
  public PaddleService $paddleService;
  public LicenseSharingService $licenseService;
  public SubscriptionManagerResult $result;

  public function __construct(SubscriptionManagerPaddle $manager)
  {
    $this->manager = $manager;
    $this->paddleService = $manager->paddleService;
    $this->licenseService = $manager->licenseService;
    $this->result = $manager->result;
  }
}
