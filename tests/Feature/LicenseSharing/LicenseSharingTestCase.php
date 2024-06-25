<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharing;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\LicenseSharing\LicenseSharingService;
use Tests\ApiTestCase;

class LicenseSharingTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/account/license-sharings';
  public string $model = LicenseSharing::class;

  public LicenseSharingService $service;
  public SubscriptionManager $manager;

  protected function setUp(): void
  {
    parent::setUp();
    $this->service = app(LicenseSharingService::class);
    $this->manager = app(SubscriptionManager::class);
  }
}
