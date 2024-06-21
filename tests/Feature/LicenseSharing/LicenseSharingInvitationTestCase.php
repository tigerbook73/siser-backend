<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Services\LicenseSharing\LicenseSharingService;
use Tests\ApiTestCase;

class LicenseSharingInvitationTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/account/license-sharing-invitations';
  public string $model = LicenseSharingInvitation::class;

  public LicenseSharingService $service;

  protected function setUp(): void
  {
    parent::setUp();
    $this->service = app(LicenseSharingService::class);
  }
}
