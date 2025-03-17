<?php

namespace App\Services\LicenseSharing;

use App\Models\LicenseSharing;
use App\Models\LicenseSharingInvitation;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\LicenseSharingNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;


class LicenseSharingService
{
  public function validateCreateLicenseSharing(Subscription $subscription): void
  {
    if ($subscription->getStatus() !== Subscription::STATUS_ACTIVE) {
      throw new \Exception('Invalid subscription status');
    }

    if ($subscription->subscription_level <= 1) {
      throw new \Exception('Invalid subscription level');
    }

    if (!$subscription->hasLicensePackageInfo()) {
      throw new \Exception('Invalid license package info');
    }

    if ($subscription->user->getActiveLicenseSharing()) {
      throw new \Exception('User already has active license sharing');
    }
  }

  /**
   * Create license sharing model for user
   *
   * This function shall be called when a new subscription is activated
   */
  public function createLicenseSharing(Subscription $subscription): LicenseSharing
  {
    /**
     * Steps:
     * 1. create license sharing
     * 2. update license count for owner
     */

    $this->validateCreateLicenseSharing($subscription);

    $licenseSharing = LicenseSharing::createFromSubscription($subscription);
    $licenseSharing->user->updateSubscriptionLevel();

    return $licenseSharing;
  }

  /**
   * Update license sharing
   *
   * This function shall be called when subscription is updated or license sharing invitation is created/cancelled/revoked
   */
  public function refreshLicenseSharing(LicenseSharing $licenseSharing): LicenseSharing
  {
    /**
     * steps:
     * 1. update license sharing from its original subscription and invitations
     * 2. revoke extra license sharing invitations (if total license count reduced)
     * 3. update license sharing invitations (if product name or subscription level changed)
     * 4. update owner data
     */

    if ($licenseSharing->getStatus() !== LicenseSharing::STATUS_ACTIVE) {
      throw new \Exception('Invalid license sharing status');
    }

    $licenseSharing->updateFromSubscripton();
    $this->updateLicenseSharingUsage($licenseSharing);
    $licenseSharing->user->updateSubscriptionLevel();

    return $licenseSharing;
  }


  /**
   * Revoke extra license sharing invitations and update existing invitations
   *
   * This function shall be called when a license sharing is updated
   */
  protected function updateLicenseSharingUsage(LicenseSharing $licenseSharing)
  {
    $totalCount = $licenseSharing->total_count;

    /** @var Collection|LicenseSharingInvitation[] $invitations */
    $invitations = $licenseSharing->active_license_sharing_invitations()
      ->orderBy('expires_at', 'desc')
      ->get();

    // first $totalCount invitations are valid, the rest shall be revoked
    $invitationsToKeep = $invitations->slice(0, $totalCount);
    $invitationsToRevoke = $invitations->slice($totalCount);

    foreach ($invitationsToRevoke as $invitation) {
      // NOTE: shall not call $this->revokeLicenseSharingInvitation() because it may cause recursive call
      $this->revokeLicenseSharingInvitationWithoutUpdateLicenseSharing($invitation);
    }

    foreach ($invitationsToKeep as $invitation) {
      $invitation->product_name = $licenseSharing->product_name;
      $invitation->subscription_level = $licenseSharing->subscription_level;
      $invitation->save();
    }

    $licenseSharing->used_count = $invitationsToKeep->count();
    $licenseSharing->free_count = $licenseSharing->total_count - $licenseSharing->used_count;
    $licenseSharing->save();

    return $licenseSharing;
  }


  public function updateLicenseSharingFromSubscription(LicenseSharing $licenseSharing, Subscription $subscription)
  {
    /**
     * steps:
     * 1. update license sharing from its new subscription (when subscription upgraded) and invitations
     * 2. revoke extra license sharing invitations (if total license count reduced)
     * 3. update license sharing invitations (if product name or subscription level changed)
     * 4. update owner data
     */

    $licenseSharing->updateFromSubscripton($subscription);
    $this->updateLicenseSharingUsage($licenseSharing);
    $licenseSharing->user->updateSubscriptionLevel();

    return $licenseSharing;
  }

  protected function validateCreateLicenseSharingInvitation(LicenseSharing $licenseSharing, User $guest, $sharingExpiresAt): void
  {
    if ($guest->subscription_level >= 2) {
      throw new \Exception('Guest already has a pro license');
    }

    if ($licenseSharing->getStatus() !== LicenseSharing::STATUS_ACTIVE) {
      throw new \Exception('Invalid license sharing status');
    }

    if ($licenseSharing->free_count <= 0) {
      throw new \Exception('No free license available');
    }

    if ($licenseSharing->user_id === $guest->id) {
      throw new \Exception('Owner cannot invite himself');
    }

    if ($licenseSharing->active_license_sharing_invitations()->where('guest_id', $guest->id)->exists()) {
      throw new \Exception('User already invited');
    }

    if (Carbon::parse($sharingExpiresAt)->isPast()) {
      throw new \Exception('Invalid sharing expiration date');
    }
  }

  /**
   * Create license sharing invitation
   *
   * This function shall be called when a user is invited to a shared license
   */
  public function createLicenseSharingInvitation(LicenseSharing $licenseSharing, User $guest, $sharingExpiresAt)
  {
    /**
     * Validataion shall be done before calling this function
     *
     * steps:
     * 1. create license sharing invitation
     * 2. update license sharing
     * 3. notify guest
     */

    $sharingExpiresAt = $sharingExpiresAt ?? '2099-12-31';
    $this->validateCreateLicenseSharingInvitation($licenseSharing, $guest, $sharingExpiresAt);

    $invitation = LicenseSharingInvitation::createNew($licenseSharing, $guest, $sharingExpiresAt);
    $this->refreshLicenseSharing($licenseSharing);
    $invitation->notifyGuest(LicenseSharingNotification::NOTIF_NEW_INVITATION);

    return $invitation;
  }

  public function acceptLicenseSharingInvitation(LicenseSharingInvitation $invitation)
  {
    /**
     * Validataion shall be done before calling this function
     *
     * steps:
     * 1. cancel other open invitations of the guest
     * 2. update invitation
     * 3. update owner data
     * 4. update guest data
     */

    if ($invitation->status !== LicenseSharingInvitation::STATUS_OPEN) {
      throw new \Exception('Invalid invitation status');
    }

    // cancel other open invitations
    foreach ($invitation->guest->getOpenLicenseSharingInvitation() as $otherInvitation) {
      if ($otherInvitation->id != $invitation->id) {
        $this->cancelLicenseSharingInvitation($otherInvitation);
      }
    };

    $invitation->accept();
    $this->refreshLicenseSharing($invitation->license_sharing);
    $invitation->guest->updateSubscriptionLevel();

    return $invitation;
  }

  public function cancelLicenseSharingInvitation(LicenseSharingInvitation $invitation)
  {
    /**
     * Validataion shall be done before calling this function
     *
     * steps:
     * 1. cancel invitation
     * 2. update owner data
     * 3. update guest data
     */

    if (
      $invitation->status !== LicenseSharingInvitation::STATUS_OPEN &&
      $invitation->status !== LicenseSharingInvitation::STATUS_ACCEPTED
    ) {
      throw new \Exception('Invalid invitation status');
    }

    $oldStatus = $invitation->status;

    $invitation->cancel();
    $this->refreshLicenseSharing($invitation->license_sharing);
    $invitation->guest->updateSubscriptionLevel();

    // only send notification when previous status is active
    if ($oldStatus === LicenseSharingInvitation::STATUS_ACCEPTED) {
      $invitation->notifyGuest(LicenseSharingNotification::NOTIF_INVITATION_CANCELLED);
    }

    return $invitation;
  }

  public function revokeLicenseSharingInvitation(LicenseSharingInvitation $invitation)
  {
    /**
     * Validataion shall be done before calling this function
     *
     * steps:
     * 1. update invitation
     * 2. update owner data
     * 3. update guest data
     */

    if (
      $invitation->status !== LicenseSharingInvitation::STATUS_OPEN &&
      $invitation->status !== LicenseSharingInvitation::STATUS_ACCEPTED
    ) {
      throw new \Exception('Invalid invitation status');
    }

    $oldStatus = $invitation->status;

    $invitation->revoke();
    $this->refreshLicenseSharing($invitation->license_sharing);
    $invitation->guest->updateSubscriptionLevel();

    // only send notification when previous status is active
    if ($oldStatus === LicenseSharingInvitation::STATUS_ACCEPTED) {
      $invitation->notifyGuest(LicenseSharingNotification::NOTIF_INVITATION_REVOKED);
    }

    return $invitation;
  }

  protected function revokeLicenseSharingInvitationWithoutUpdateLicenseSharing(LicenseSharingInvitation $invitation)
  {
    /**
     * Validataion shall be done before calling this function
     *
     * steps:
     * 1. revoke invitation
     * 2. update guest data
     */

    if (
      $invitation->status !== LicenseSharingInvitation::STATUS_OPEN &&
      $invitation->status !== LicenseSharingInvitation::STATUS_ACCEPTED
    ) {
      throw new \Exception('Invalid invitation status');
    }

    $oldStatus = $invitation->status;

    $invitation->revoke();
    $invitation->guest->updateSubscriptionLevel();

    // only send notification when previous status is active
    if ($oldStatus === LicenseSharingInvitation::STATUS_ACCEPTED) {
      $invitation->notifyGuest(LicenseSharingNotification::NOTIF_INVITATION_REVOKED);
    }

    return $invitation;
  }

  public function expireLicenseSharingInvitation(LicenseSharingInvitation $invitation)
  {
    /**
     * Validataion shall be done before calling this function
     *
     * steps:
     * 1. update invitation
     * 2. update owner data
     * 3. update guest data
     */
    if (
      $invitation->status !== LicenseSharingInvitation::STATUS_OPEN &&
      $invitation->status !== LicenseSharingInvitation::STATUS_ACCEPTED
    ) {
      throw new \Exception('Invalid invitation status');
    }

    if ($invitation->expires_at->isFuture()) {
      throw new \Exception('Can not expire future invitation');
    }


    $invitation->expire();
    $this->refreshLicenseSharing($invitation->license_sharing);
    $invitation->guest->updateSubscriptionLevel();

    $invitation->notifyGuest(LicenseSharingNotification::NOTIF_INVITATION_EXPIRED);

    return $invitation;
  }

  public function deleteLicenseSharingInvitation(LicenseSharingInvitation $invitation)
  {
    /**
     * steps:
     * 1. revoke invitation if required
     * 2. mark invitation as deleted
     */
    if ($invitation->status === LicenseSharingInvitation::STATUS_DELETED) {
      throw new \Exception('Invalid invitation status');
    }

    if (
      $invitation->status === LicenseSharingInvitation::STATUS_ACCEPTED ||
      $invitation->status === LicenseSharingInvitation::STATUS_OPEN
    ) {
      $this->revokeLicenseSharingInvitation($invitation);
    }

    $invitation->markAsDeleted();
    return $invitation;
  }
}
