<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Carbon\Carbon;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingCommandExpireTest extends LicenseSharingTestCase
{
  public ?string $role = 'admin';


  public function test_expire_command_success()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(
      ownerName: 'user1.test',
      count: 2,
      guestName: 'user2.test',
      expiresAt: now()->addDays(1)
    );

    Carbon::setTestNow('2023-01-03 00:00:00');
    $this->artisan('license-sharing:cmd expire')->assertSuccessful();

    $invitation->refresh();
    $this->assertTrue($invitation->status == LicenseSharingInvitation::STATUS_EXPIRED);
  }

  public function test_expire_command_ok_none()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(
      ownerName: 'user1.test',
      count: 2,
      guestName: 'user2.test',
      expiresAt: now()->addDays(1)
    );

    Carbon::setTestNow('2023-01-01 00:00:00');
    $this->artisan('license-sharing:cmd expire')->assertSuccessful();

    $invitation->refresh();
    $this->assertTrue($invitation->status !== LicenseSharingInvitation::STATUS_EXPIRED);
  }
}
