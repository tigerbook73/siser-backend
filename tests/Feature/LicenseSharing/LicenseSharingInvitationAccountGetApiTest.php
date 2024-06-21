<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationAccountGetApiTest extends LicenseSharingInvitationTestCase
{
  public ?string $role = 'customer';
  public User $owner;

  protected function setUp(): void
  {
    parent::setUp();
    $this->owner = User::where('name', 'user1.test')->first();
    $this->actingAs($this->owner, 'api');
  }

  public function test_get_ok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);
    $this->getAssert(id: $invitation->id);

    $this->service->acceptLicenseSharingInvitation($invitation);
    $this->getAssert(id: $invitation->id);
  }

  public function test_get_nok()
  {
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(ownerName: $this->owner->name);

    $this->getAssert(status: 404);

    $this->service->deleteLicenseSharingInvitation($invitation);
    $this->getAssert(status: 404, id: $invitation->id);
  }
}
