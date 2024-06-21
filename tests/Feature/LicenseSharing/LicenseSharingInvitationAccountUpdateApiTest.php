<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationAccountUpdateApiTest extends LicenseSharingInvitationTestCase
{
  public ?string $role = 'customer';
  public User $owner;

  protected function setUp(): void
  {
    parent::setUp();
    $this->owner = User::where('name', 'user1.test')->first();
    $this->actingAs($this->owner, 'api');
  }

  public function test_update_expires_at_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);

    $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'expires_at' => '2088-12-31',
    ]);
    $response->assertOk();
  }

  public function test_update_expires_at_ok2()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);
    $this->service->acceptLicenseSharingInvitation($invitation);

    $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'expires_at' => '2088-12-31',
    ]);
    $response->assertOk();
  }

  public function test_update_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);

    $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'expires_at' => '2088-12-31',
      'status' => LicenseSharingInvitation::STATUS_REVOKED,
    ]);
    $this->assertFailed($response);
  }

  public function test_revoke_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);

    $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'status' => LicenseSharingInvitation::STATUS_REVOKED,
    ]);
    $response->assertOk();
  }

  public function test_revoke_ok2()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);
    $this->service->acceptLicenseSharingInvitation($invitation);

    $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'status' => LicenseSharingInvitation::STATUS_REVOKED,
    ]);
    $response->assertOk();
  }
}
