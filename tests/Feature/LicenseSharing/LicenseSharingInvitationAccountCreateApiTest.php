<?php

namespace Tests\Feature\LicenseSharing;

use App\Models\LicenseSharingInvitation;
use App\Models\User;
use Tests\Feature\LicenseSharing\LicenseSharingInvitationTestCase;
use Tests\Helper\LicenseSharingTestHelper;

class LicenseSharingInvitationAccountCreateApiTest extends LicenseSharingInvitationTestCase
{
  public ?string $role = 'customer';
  public User $owner;

  protected function setUp(): void
  {
    parent::setUp();
    $this->owner = User::where('name', 'user1.test')->first();
    $this->actingAs($this->owner, 'api');
  }

  public function test_create_ok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: $this->owner->name);

    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(201);
  }

  public function test_create_default_expires_at_ok()
  {
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: $this->owner->name);

    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
    ]);
    $response->assertStatus(201);
  }

  public function test_create_params_validate_nok()
  {
    // no guest email
    $response = $this->postJson("{$this->baseUrl}", [
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(422);

    // guest email format invalid
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(422);

    // expires at in valid
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
      'expires_at' => now()->subDay()->format('Y-m-d'),
    ]);
    $response->assertStatus(422);
  }

  public function test_create_more_validate_nok()
  {
    // no license sharing
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(400);

    /**
     * create license sharing
     */
    $licenseSharing = LicenseSharingTestHelper::createFakeLicenseSharing(ownerName: $this->owner->name);

    // share to non-existing user
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user#.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(400);

    // share to self
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user1.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(400);

    // share twice
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(201);
    $invitation = LicenseSharingInvitation::find($response->json('id'));

    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(400);

    // share to pro
    $this->service->acceptLicenseSharingInvitation($invitation);
    $response = $this->postJson("{$this->baseUrl}", [
      'guest_email' => 'user2.test@iifuture.com',
      'expires_at' => '2099-12-31',
    ]);
    $response->assertStatus(400);
  }
}
