<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationAccountDeleteApiTest extends LicenseSharingInvitationTestCase
{
  public ?string $role = 'customer';
  public User $owner;

  protected function setUp(): void
  {
    parent::setUp();
    $this->owner = User::where('name', 'user1.test')->first();
    $this->actingAs($this->owner, 'api');
  }

  public function test_delete_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);

    $response = $this->deleteJson("{$this->baseUrl}/{$invitation->id}");
    $response->assertNoContent();
  }

  public function test_delete_ok2()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);
    $this->service->acceptLicenseSharingInvitation($invitation);

    $response = $this->deleteJson("{$this->baseUrl}/{$invitation->id}");
    $response->assertNoContent();
  }

  public function test_delete_ok4()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);
    $this->service->cancelLicenseSharingInvitation($invitation);

    $response = $this->deleteJson("{$this->baseUrl}/{$invitation->id}");
    $response->assertNoContent();
  }

  public function test_delete_ok5()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);
    $this->service->revokeLicenseSharingInvitation($invitation);

    $response = $this->deleteJson("{$this->baseUrl}/{$invitation->id}");
    $response->assertNoContent();
  }
}
