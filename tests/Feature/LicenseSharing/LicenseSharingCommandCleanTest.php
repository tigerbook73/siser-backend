<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Carbon\Carbon;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingCommandCleanTest extends LicenseSharingTestCase
{
  public ?string $role = 'admin';


  public function test_clean_command_success()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(
      ownerName: 'user1.test',
      count: 2,
      guestName: 'user2.test',
    );
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    Carbon::setTestNow('2023-01-03 00:00:00');
    $this->artisan('license-sharing:cmd clean')->assertSuccessful();;

    $this->assertNull($invitation->fresh());
  }

  public function test_clean_command_cooling_period_ok()
  {
    Carbon::setTestNow('2023-01-01 00:00:00');
    $invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation(
      ownerName: 'user1.test',
      count: 2,
      guestName: 'user2.test',
    );
    $invitation = $this->service->deleteLicenseSharingInvitation($invitation);

    Carbon::setTestNow('2023-01-01 00:00:01');
    $this->artisan('license-sharing:cmd clean')->assertSuccessful();

    $this->assertNotNull($invitation->fresh());
  }
}
