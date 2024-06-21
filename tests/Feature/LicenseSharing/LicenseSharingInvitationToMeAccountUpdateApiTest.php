<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationToMeTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationToMeAccountUpdateApiTest extends LicenseSharingInvitationToMeTestCase
{
  public ?string $role = 'customer';
  public User $guest;

  protected function setUp(): void
  {
    parent::setUp();
    $this->guest = User::where('name', 'user2.test')->first();
    $this->actingAs($this->guest, 'api');
  }

  public function test_cancel_open_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'status' => LicenseSharingInvitation::STATUS_CANCELLED,
    ])->assertOk();

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing->refresh());
  }

  public function test_cancel_other_status_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    foreach ([
      LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingInvitation::STATUS_DELETED,
      LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingInvitation::STATUS_REVOKED,
    ] as $status) {
      $invitation->status = $status;
      $invitation->save();
      $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
        'status' => LicenseSharingInvitation::STATUS_CANCELLED,
      ]);
      $this->assertFailed($response);
    }
  }

  public function test_cancel_accepted_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $this->service->acceptLicenseSharingInvitation($invitation);

    $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'status' => LicenseSharingInvitation::STATUS_CANCELLED,
    ])->assertOk();

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing->refresh());
  }

  public function test_accept_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    $this->postJson("{$this->baseUrl}/{$invitation->id}", [
      'status' => LicenseSharingInvitation::STATUS_ACCEPTED,
    ])->assertOk();

    LicenseSharingTestHelper::assertLicenseSharing($invitation->license_sharing->refresh());
  }

  public function test_accept_and_cancel_other_ok()
  {
    $licenseSharing1 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user1.test');
    $licenseSharing2 = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: 'user3.test');

    $invitation1 = $this->service->createLicenseSharingInvitation($licenseSharing1, $this->guest, '2099-12-31');
    $invitation2 = $this->service->createLicenseSharingInvitation($licenseSharing2, $this->guest, '2099-12-31');

    $this->postJson("{$this->baseUrl}/{$invitation1->id}", [
      'status' => LicenseSharingInvitation::STATUS_ACCEPTED,
    ])->assertOk();

    $this->assertEquals(LicenseSharingInvitation::STATUS_CANCELLED, $invitation2->refresh()->status);

    LicenseSharingTestHelper::assertLicenseSharing($invitation1->license_sharing->refresh());
    LicenseSharingTestHelper::assertLicenseSharing($invitation2->license_sharing->refresh());
  }

  public function test_accept_other_status_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();

    foreach ([
      LicenseSharingInvitation::STATUS_ACCEPTED,
      LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingInvitation::STATUS_DELETED,
      LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingInvitation::STATUS_REVOKED,
    ] as $status) {
      $invitation->status = $status;
      $invitation->save();
      $response = $this->postJson("{$this->baseUrl}/{$invitation->id}", [
        'status' => LicenseSharingInvitation::STATUS_ACCEPTED,
      ]);
      $this->assertFailed($response);
    }
  }
}
