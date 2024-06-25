<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\LicenseSharing\LicenseSharingService;
use Tests\ApiTestCase;

class LicenseSharingInvitationToMeTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/account/license-sharing-invitations-to-me';
  public string $model = LicenseSharingInvitation::class;

  public LicenseSharingService $service;
  public SubscriptionManager $manager;

  protected function setUp(): void
  {
    parent::setUp();
    $this->service = app(LicenseSharingService::class);
    $this->manager = app(SubscriptionManager::class);
  }
}
