<?php

namespace Tests\Helper;

use App\Models\Coupon;
use App\Models\BillingInfo;
use App\Models\LicenseSharing;
use App\Models\Plan;
use App\Models\GeneralConfiguration;
use App\Models\LdsLicense;
use App\Models\LicensePackage;
use App\Models\LicenseSharingInvitation;
use App\Models\Subscription;
use App\Models\User;
use App\Services\LicenseSharing\LicenseSharingService;


class LicenseSharingTestHelper
{
  static public function createFakeSubscription(string $userName = 'user1.test', int $count = 2)
  {
    /** @var User $user */
    $user = User::where('name', $userName)->first();

    $plan = Plan::public()->where('interval', Plan::INTERVAL_MONTH)->first();
    $coupon = Coupon::public()->where('interval', Coupon::INTERVAL_MONTH)->first();
    $licensePackage = LicensePackage::create([
      'id' => 1,
      'type' => LicensePackage::TYPE_STANDARD,
      'name' => 'Standard License',
      'price_table' => [
        ['quantity' => 1, 'discount' => 10],
        ['quantity' => 2, 'discount' => 20],
        ['quantity' => 5, 'discount' => 30],
      ],
      'status' => LicensePackage::STATUS_ACTIVE,
    ]);

    $user->getActiveSubscription()?->stop(Subscription::STATUS_STOPPED, 'test');
    $subscription = (new Subscription())
      ->initFill()
      ->fillBillingInfo($user->billing_info ?? BillingInfo::createDefault($user))
      ->fillPlanAndCoupon($plan, $coupon, $licensePackage, 2);
    $subscription->setStatus(Subscription::STATUS_ACTIVE);
    $subscription->save();
    $subscription->user->updateSubscriptionLevel();

    return $subscription;
  }

  static public function createFakeLicenseSharing(string $ownerName = 'user1.test', int $count = 2): LicenseSharing
  {
    /** @var LicenseSharingService $service */
    $service = app(LicenseSharingService::class);

    $subscription = self::createFakeSubscription($ownerName, $count);
    $licenseSharing = $service->createLicenseSharing($subscription);

    return $licenseSharing;
  }

  static public function createFakeLicenseSharingInvitation(
    string $ownerName = 'user1.test',
    int $count = 2,
    string $guestName = 'user2.test',
    $expiresAt = '2099-12-31'
  ): LicenseSharingInvitation {

    /** @var User $guest */
    $guest = User::where('name', $guestName)->first();

    /** @var LicenseSharingService $service */
    $service = app(LicenseSharingService::class);
    $licenseSharing = self::createFakeLicenseSharing($ownerName, $count);

    $invitation = $service->createLicenseSharingInvitation($licenseSharing, $guest, $expiresAt);
    return $invitation;
  }

  /**
   * assert user's:
   * - subscription level
   * - license_count
   * - lds license count
   */
  static public function assertUser(User $user)
  {
    /**
     * case1: user have no subscription and no active invitation
     * case2: user have no subscription and have active invitation
     * case3: user have basic subscription and no active invition
     * case4: user have basic subscription and have active invition
     * case5: user have active subscription and no active license
     * case5: user have active subscription and have active license
     */
    $user->refresh();

    $activeSubscription = $user->getActiveSubscription();
    $activeLicenseSharing = $user->getActiveLicenseSharing();
    $activeInvitation = $user->getActiveLicenseSharingInvitation();
    $ldsLicense = LdsLicense::fromUserId($user->id);

    if ($user->seat_count !== $ldsLicense->license_count) {
      throw new \Exception('User license count does not match with lds license count');
    }

    if ($user->subscription_level !== $ldsLicense->subscription_level) {
      throw new \Exception('User has no subscription and no active invitation but subscription level does not match');
    }

    if (!$activeSubscription) {
      if (!$activeInvitation) {
        if ($user->subscription_level !== 0) {
          throw new \Exception('User has no subscription and no active invitation but subscription level != 0');
        }

        if ($ldsLicense->license_count !== 0) {
          throw new \Exception('User has no subscription and no active invitation but license count != 0');
        }
        return;
      }

      if ($user->subscription_level !== $activeInvitation->subscription_level) {
        throw new \Exception('User has no subscription but has active invitation but subscription level does not match');
      }

      if ($ldsLicense->license_count !== GeneralConfiguration::getMachineLicenseUnit()) {
        throw new \Exception('User has no subscription but has active invitation but license count != 2');
      }
      return;
    }

    if ($activeSubscription->subscription_level === 1) {
      if (!$activeInvitation) {
        if ($user->subscription_level !== $activeSubscription->subscription_level) {
          throw new \Exception('User has basic subscription but no active invitation but subscription level does not match');
        }

        if ($ldsLicense->license_count !== $user->machine_count * GeneralConfiguration::getMachineLicenseUnit()) {
          throw new \Exception('User has basic subscription but license count does not match');
        }
        return;
      }

      if ($user->subscription_level !== $activeInvitation->subscription_level) {
        throw new \Exception('User has basic subscription and active invitation but subscription level does not match');
      }

      if ($ldsLicense->license_count !== GeneralConfiguration::getMachineLicenseUnit()) {
        throw new \Exception('User has active subscription and active invitation but license count does not match');
      }
      return;
    }

    if ($activeSubscription->subscription_level === 2) {
      if (!$activeLicenseSharing) {
        if ($ldsLicense->license_count !== ($user->machine_count ?: 1) * GeneralConfiguration::getMachineLicenseUnit()) {
          throw new \Exception('User has active subscription but no active license sharing but license count does not match');
        }
        return;
      }

      if ($user->subscription_level !== $activeSubscription->subscription_level) {
        throw new \Exception('User has active subscription and active license sharing but subscription level does not match');
      }

      if ($ldsLicense->license_count !==  (($user->machine_count ?: 1) + $activeLicenseSharing->free_count) * GeneralConfiguration::getMachineLicenseUnit()) {
        throw new \Exception('User has active subscription and active license sharing but license count does not match');
      }
      return;
    }
  }


  /**
   * assert license sharing's:
   * - product name
   * - subscription level
   * - status
   * - total count
   * - used count
   * - free count
   *
   * assert licese sharing's invitations:
   * - see assertLicenseSharingInvitation()
   */
  static public function assertLicenseSharing(LicenseSharing $licenseSharing)
  {
    /**
     * case1: license sharing is active
     * case2: license sharing is void
     */

    $licenseSharing->refresh();
    $subscription = $licenseSharing->subscription;

    // general
    if ($licenseSharing->product_name !== $subscription->plan_info['product_name']) {
      throw new \Exception('License sharing product name does not match with subscription');
    }

    if ($licenseSharing->subscription_level !== $subscription->subscription_level) {
      throw new \Exception('License sharing subscription level does not match with subscription');
    }

    // active
    if ($licenseSharing->getStatus() == LicenseSharing::STATUS_ACTIVE) {
      if ($subscription->getStatus() !== Subscription::STATUS_ACTIVE) {
        throw new \Exception('License sharing is active but subscription is not active');
      }

      if (
        !$subscription->license_package_info ||
        $subscription->license_package_info['quantity'] !== $licenseSharing->total_count
      ) {
        throw new \Exception('License sharing total count does not match with subscription');
      }

      if ($licenseSharing->free_count !== $licenseSharing->total_count - $licenseSharing->used_count) {
        throw new \Exception('License sharing free count does not match with total count and used count');
      }

      if ($licenseSharing->used_count !== $licenseSharing->active_license_sharing_invitations()->count()) {
        throw new \Exception('License sharing used count does not match with active invitations count');
      }
    } else {
      // void
      if (
        $licenseSharing->total_count !== 0 ||
        $licenseSharing->used_count !== 0 ||
        $licenseSharing->free_count !== 0
      ) {
        throw new \Exception('License sharing is void but has total count');
      }

      if ($licenseSharing->active_license_sharing_invitations()->count() !== 0) {
        throw new \Exception('License sharing is void but has active invitations');
      }
    }

    foreach ($licenseSharing->license_sharing_invitations as $invitation) {
      self::assertLicenseSharingInvitation($invitation);
    }
  }

  /**
   * assert license sharing invitation's:
   * - product name
   * - subscription level
   * - status
   *
   * assert owner
   * assert guest
   */
  static public function assertLicenseSharingInvitation(LicenseSharingInvitation $invitation)
  {
    /**
     * case1: invitation is active
     * case2: invitation is accepted
     * case3: invitation is cancelled
     * case4: invitation is revoked
     */
    $invitation->refresh();

    $licenseSharing = $invitation->license_sharing;

    if (in_array($invitation->getStatus(), [LicenseSharingInvitation::STATUS_ACCEPTED, LicenseSharingInvitation::STATUS_OPEN])) {
      if ($licenseSharing->getStatus() !== LicenseSharing::STATUS_ACTIVE) {
        throw new \Exception('License sharing invitation is active but license sharing is not active');
      }

      if ($invitation->product_name != $licenseSharing->product_name) {
        throw new \Exception('License sharing invitation product name does not match with license sharing');
      }

      if ($invitation->subscription_level !== $licenseSharing->subscription_level) {
        throw new \Exception('License sharing invitation subscription level does not match with license sharing');
      }
    } else {
      // do nothing
    }

    self::assertUser($invitation->owner);
    self::assertUser($invitation->guest);
  }
}
