<?php

namespace App\Http\Controllers\Test;

use App\Models\LicenseSharing;
use App\Models\LicenseSharingInvitation;
use App\Models\Subscription;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingNotificationTest
{
  public function __construct(
    public LicenseSharingInvitation|null $invitation = null,
    public Subscription|null $subscription = null,
    public LicenseSharing|null $licenseSharing = null,
  ) {
  }

  static public function init(): self
  {
    $test = new self();
    $test->invitation = LicenseSharingTestHelper::createFakeLicenseSharingInvitation();
    $test->licenseSharing = $test->invitation->license_sharing;
    $test->subscription = $test->licenseSharing->subscription;
    return $test;
  }

  static public function clean()
  {
    foreach (LicenseSharingInvitation::all() as $invitation) {
      $invitation->delete();
      $invitation->license_sharing->delete();
      $invitation->license_sharing->subscription->delete();

      $invitation->guest->updateSubscriptionLevel();
      $invitation->owner->updateSubscriptionLevel();
    }
  }
}
