<?php

namespace App\Models;

use App\Models\Base\LicenseSharing as BaseLicenseSharing;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property  Collection|LicenseSharingInvitation[] $active_license_sharing_invitations
 */
class LicenseSharing extends BaseLicenseSharing
{
  const STATUS_ACTIVE = 'active';
  const STATUS_VOID   = 'void';

  static protected $attributesOption = [
    'id'                    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'               => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_id'       => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'product_name'          => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'    => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'total_count'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'used_count'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'free_count'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'status'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 1, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_0],
    'created_at'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];


  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): self
  {
    if (!in_array($status, [self::STATUS_ACTIVE, self::STATUS_VOID])) {
      throw new \Exception('Invalid status');
    }
    $this->status = $status;
    return $this;
  }

  /**
   * use cases
   * 1. When purchasing a package, create a LicenseSharing
   * 2. When upgrade or downgrade a package, update the LicenseSharing (...)
   * 3. When cancel a package, void the LicenseSharing
   * 4. When send a invitation, allocate a license
   * 5. When reject/cancel/revoke a invitation, free a license
   */

  static public function createFromSubscription(Subscription $subscription)
  {
    /**
     * validation
     *
     * 1. status == active
     * 2. subscription_level > 1
     * 3. license_package is not null
     */
    if ($subscription->status !== Subscription::STATUS_ACTIVE) {
      throw new \Exception('Invalid subscription status');
    }
    if ($subscription->subscription_level <= 1) {
      throw new \Exception('Invalid subscription level');
    }
    if (!$subscription->hasLicensePackageInfo()) {
      throw new \Exception('Invalid license package');
    }

    $licenseSharing = new LicenseSharing();
    $licenseSharing->user_id            = $subscription->user_id;
    $licenseSharing->subscription_id    = $subscription->id;
    $licenseSharing->product_name       = $subscription->getPlanInfo()->product_name;
    $licenseSharing->subscription_level = $subscription->subscription_level;
    $licenseSharing->total_count        = $subscription->getLicensePackageInfo()?->price_rate->quantity ?? 0;
    $licenseSharing->free_count         = $licenseSharing->total_count;
    $licenseSharing->used_count         = 0;
    $licenseSharing->setStatus(self::STATUS_ACTIVE);
    $licenseSharing->save();
    return $licenseSharing;
  }

  /**
   * try to update from subscription
   *
   * @param ?Subscription $subscription
   *  - If $subscription is null, use the current subscription (can be inactive).
   *  - Otherwise use the given subscription. The given subscription must be active, subscription_level > 1, and license_package is not null.
   */
  public function updateFromSubscripton(?Subscription $subscription = null)
  {
    if ($this->status === self::STATUS_VOID) {
      throw new \Exception('Invalid status');
    }

    if ($subscription) {
      if ($subscription->status !== Subscription::STATUS_ACTIVE) {
        throw new \Exception('Invalid subscription status');
      }
      if ($subscription->subscription_level <= 1) {
        throw new \Exception('Invalid subscription level');
      }
      if (!$subscription->hasLicensePackageInfo()) {
        throw new \Exception('Invalid license package');
      }
      if ($this->user_id !== $subscription->user_id) {
        throw new \Exception('Try to update from subscription of another user!');
      }
    }

    $subscription = $subscription ?? $this->subscription;
    $usedCount = $this->active_license_sharing_invitations()->count();

    $this->subscription_id    = $subscription->id;
    $this->product_name       = $subscription->getPlanInfo()->product_name;
    $this->subscription_level = $subscription->subscription_level;
    $this->total_count        = ($subscription->status == Subscription::STATUS_ACTIVE) ?
      ($subscription->getLicensePackageInfo()?->price_rate->quantity ?? 0) :
      0;
    $this->used_count         = $usedCount;
    $this->free_count         = $this->total_count > $this->used_count ? $this->total_count - $this->used_count : 0;

    if ($this->total_count == 0) {
      $this->setStatus(self::STATUS_VOID);
    }
    $this->save();

    return $this;
  }


  public function active_license_sharing_invitations()
  {
    return $this->license_sharing_invitations()->whereIn(
      'status',
      [LicenseSharingInvitation::STATUS_OPEN, LicenseSharingInvitation::STATUS_ACCEPTED]
    );
  }
}
