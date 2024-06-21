<?php

namespace Tests\Feature\Email;

use App\Notifications\LicenseSharingNotification;
use Tests\ApiTestCase;

class LicenseSharingNotifitionBladeTest extends ApiTestCase
{
  public string $baseUrl = '/be-test/notification/license-sharing';
  public ?string $role = 'customer';


  public function viewNotification(string $type)
  {
    $response =  $this->get("{$this->baseUrl}/{$type}/view");
    $response->assertStatus(200)
      ->assertSeeText('Team Siser')
      ->assertDontSeeText('messages.');
  }

  public function clean()
  {
    return $this->get("/be-test/notification/license-sharing-clean");
  }

  public function testNotificationLicenseSharingNewInvitation()
  {
    $this->viewNotification(LicenseSharingNotification::NOTIF_NEW_INVITATION);
  }

  public function testNotificationLicenseSharingInvitationCalcelled()
  {
    $this->viewNotification(LicenseSharingNotification::NOTIF_INVITATION_CANCELLED);
  }

  public function testNotificationInvitationExpired()
  {
    $this->viewNotification(LicenseSharingNotification::NOTIF_INVITATION_EXPIRED);
  }

  public function testNotificationInvitationRevoked()
  {
    $this->viewNotification(LicenseSharingNotification::NOTIF_INVITATION_REVOKED);
  }
}
