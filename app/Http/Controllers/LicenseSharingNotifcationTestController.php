<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Test\LicenseSharingNotificationTest;
use Illuminate\Http\Request;
use App\Http\Controllers\Test\SubscriptionNotificationTest;
use App\Models\LicenseSharingInvitation;
use App\Notifications\LicenseSharingNotification;

class LicenseSharingNotifcationTestController extends Controller
{
  public function clean()
  {
    SubscriptionNotificationTest::clean();
    return "Cleaned!";
  }


  public function sendMail(Request $request, string $type)
  {
    $licenseSharingTest = LicenseSharingNotificationTest::init();

    $TYPE_STATUS_MAP = [
      LicenseSharingNotification::NOTIF_NEW_INVITATION => LicenseSharingInvitation::STATUS_OPEN,
      LicenseSharingNotification::NOTIF_INVITATION_EXPIRED => LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingNotification::NOTIF_INVITATION_CANCELLED => LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingNotification::NOTIF_INVITATION_REVOKED => LicenseSharingInvitation::STATUS_REVOKED,
    ];

    if (!isset($TYPE_STATUS_MAP[$type])) {
      return new \Exception('Invalid type');
    }

    $licenseSharingTest->invitation->status = $TYPE_STATUS_MAP[$type];
    $licenseSharingTest->invitation->save();

    $licenseSharingTest->invitation->guest->notify(new LicenseSharingNotification(($type), $licenseSharingTest->invitation));

    return response('Please checkout your email');
  }

  public function viewNotification(Request $request, string $type)
  {
    $licenseSharingTest = LicenseSharingNotificationTest::init();

    $TYPE_STATUS_MAP = [
      LicenseSharingNotification::NOTIF_NEW_INVITATION => LicenseSharingInvitation::STATUS_OPEN,
      LicenseSharingNotification::NOTIF_INVITATION_EXPIRED => LicenseSharingInvitation::STATUS_EXPIRED,
      LicenseSharingNotification::NOTIF_INVITATION_CANCELLED => LicenseSharingInvitation::STATUS_CANCELLED,
      LicenseSharingNotification::NOTIF_INVITATION_REVOKED => LicenseSharingInvitation::STATUS_REVOKED,
    ];

    $licenseSharingTest->invitation->status = $TYPE_STATUS_MAP[$type];
    $licenseSharingTest->invitation->save();

    if (!isset($TYPE_STATUS_MAP[$type])) {
      return new \Exception('Invalid type');
    }

    return (new LicenseSharingNotification(($type), $licenseSharingTest->invitation))
      ->toMail($licenseSharingTest->invitation->guest);
  }
}
