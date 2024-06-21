<?php

namespace App\Models;

use App\Models\Base\LicenseSharingInvitation as BaseLicenseSharingInvitation;
use App\Notifications\LicenseSharingNotification;
use Carbon\Carbon;

class LicenseSharingInvitation extends BaseLicenseSharingInvitation
{
  /**
   * Invitation statuses
   * -- when a open invitation is cancelled by the owner, it will be removed from the database
   */
  const STATUS_INIT             = 'init';             // init status
  const STATUS_OPEN             = 'open';             // Invitation is open for the guest to accept or reject
  const STATUS_ACCEPTED         = 'accepted';         // Invitation has been accepted by the guest
  const STATUS_CANCELLED        = 'cancelled';        // Invitation has been cancelled by the guest
  const STATUS_REVOKED          = 'revoked';          // Invitation has been revoke by the owner
  const STATUS_EXPIRED          = 'expired';          // Invitation has expired
  const STATUS_DELETED          = 'deleted';          // Invitation is pending for deletion

  const STATUS_TRANSITION_MATRIX = [
    self::STATUS_INIT       =>  [self::STATUS_OPEN],
    self::STATUS_OPEN       =>  [self::STATUS_ACCEPTED, self::STATUS_CANCELLED, self::STATUS_REVOKED, self::STATUS_EXPIRED],
    self::STATUS_ACCEPTED   =>  [self::STATUS_CANCELLED, self::STATUS_REVOKED, self::STATUS_EXPIRED],
    self::STATUS_CANCELLED  =>  [self::STATUS_DELETED],
    self::STATUS_REVOKED    =>  [self::STATUS_DELETED],
    self::STATUS_EXPIRED    =>  [self::STATUS_DELETED],
    self::STATUS_DELETED    =>  [],
  ];

  static protected $attributesOption = [
    'id'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_sharing_id'    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'product_name'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'owner_id'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'owner_name'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'owner_email'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'guest_id'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'guest_name'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'guest_email'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'expires_at'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'status'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'updated_at'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
  ];


  public function isStatusTransitionValid(?string $from, string $to)
  {
    if (
      !isset(self::STATUS_TRANSITION_MATRIX[$from ?? self::STATUS_INIT]) ||
      !isset(self::STATUS_TRANSITION_MATRIX[$to]) ||
      !in_array($to, self::STATUS_TRANSITION_MATRIX[$from])
    ) {
      return false;
    }

    return true;
  }

  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): static
  {
    $current = $this->status ?? self::STATUS_INIT;
    if (!self::isStatusTransitionValid($current, $status)) {
      throw new \Exception('Invalid status transition from ' . $current . ' to ' . $status);
    }

    $this->status = $status;
    $logs = $this->logs ?? [];
    $logs[] = ['from' => $current, 'to' => $status, 'timestamp' => now()];
    $this->logs = $logs;
    return $this;
  }

  public function open()
  {
    $this->setStatus(self::STATUS_OPEN)->save();
  }

  public function accept()
  {
    $this->setStatus(self::STATUS_ACCEPTED)->save();
  }

  public function cancel()
  {
    $this->setStatus(self::STATUS_CANCELLED)->save();
  }

  public function revoke()
  {
    $this->setStatus(self::STATUS_REVOKED)->save();
  }

  public function expire()
  {
    $this->setStatus(self::STATUS_EXPIRED)->save();
  }

  public function markAsDeleted()
  {
    $this->setStatus(self::STATUS_DELETED)->save();
  }

  static public function createNew(
    LicenseSharing $licenseSharing,
    User $guest,
    Carbon|string $expires_at = null
  ): self {
    $invitation = (new self());
    $invitation->license_sharing_id   = $licenseSharing->id;
    $invitation->product_name         = $licenseSharing->product_name;
    $invitation->subscription_level   = $licenseSharing->subscription_level;
    $invitation->owner_id             = $licenseSharing->user_id;
    $invitation->owner_name           = $licenseSharing->user->name;
    $invitation->owner_email          = $licenseSharing->user->email;
    $invitation->guest_id             = $guest->id;
    $invitation->guest_name           = $guest->name;
    $invitation->guest_email          = $guest->email;
    $invitation->expires_at           = Carbon::parse($expires_at ?? '2099-12-31');
    $invitation->setStatus(self::STATUS_OPEN);
    $invitation->save();

    return $invitation;
  }

  public function notifyGuest(string $type)
  {
    $this->guest->notify(new LicenseSharingNotification($type, $this));
  }
}
