<?php

namespace App\Models;

use App\Events\UserSubscriptionLevelChanged;
use App\Services\Cognito\CognitoUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

class User extends UserWithTrait
{
  use Notifiable;
  use TraitDrAttr;

  // user_type
  public const TYPE_NORMAL        = 'normal';
  public const TYPE_STAFF         = 'staff';
  public const TYPE_VIP           = 'vip';
  public const TYPE_BLACKLISTED   = 'blacklisted';

  // DR
  public const DR_CUSTOMER_ID     = 'customer_id';

  static protected $attributesOption = [
    'id'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'cognito_id'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'given_name'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'family_name'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'full_name'           => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'email'               => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'phone_number'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'country_code'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'language_code'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'timezone'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'subscription_level'  => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'license_count'       => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'type'                => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];


  public function newQuery()
  {
    return parent::newQuery()->whereNotNull('cognito_id');
  }

  static public function typeFromEmail(string $email)
  {
    $type = User::TYPE_NORMAL;
    if (config('siser.staff_emails_auto')) {
      $email = strtolower($email);
      foreach (config('siser.staff_emails') as $emailDomain) {
        if (str_ends_with($email, $emailDomain)) {
          $type = User::TYPE_STAFF;
          break;
        }
      }
    }
    return $type;
  }

  protected function beforeCreate()
  {
    $this->subscription_level = 0;
    $this->license_count = 0;
    $this->roles = null;

    if (!$this->type) {
      $this->type = User::typeFromEmail($this->email);
    }
  }

  protected function afterCreate()
  {
    LdsLicense::createFromUser($this);
  }

  protected function afterUpdate()
  {
    if ($this->wasChanged('subscription_level')) {
      UserSubscriptionLevelChanged::dispatch($this);
    }
    if ($this->wasChanged(['subscription_level', 'license_count'])) {
      $this->lds_license->updateSubscriptionLevel($this->subscription_level, $this->license_count);
    };
  }

  public function getDrCustomerId(): string|null
  {
    return $this->getDrAttr(User::DR_CUSTOMER_ID);
  }

  public function setDrCustomerId(string $drCustomerId): self
  {
    return $this->setDrAttr(User::DR_CUSTOMER_ID, $drCustomerId);
  }

  static public function createOrUpdateFromCognitoUser(CognitoUser $cognitoUser): User
  {
    /** @var User|null $user */
    $user = User::where('cognito_id', $cognitoUser->id)->first();
    if ($user) {
      $user->updateFromCognitoUser($cognitoUser);
    } else {
      $user = User::create([
        'id'            => $cognitoUser->software_user_id,
        'name'          => $cognitoUser->username,
        'cognito_id'    => $cognitoUser->id,
        'email'         => $cognitoUser->email,
        'given_name'    => $cognitoUser->given_name,
        'family_name'   => $cognitoUser->family_name,
        'full_name'     => $cognitoUser->full_name,
        'phone_number'  => $cognitoUser->phone_number,
        'country_code'  => $cognitoUser->country_code,
        'language_code' => $cognitoUser->language_code,
        'timezone'      => $cognitoUser->timezone,
        'password'      => 'not allowed',
      ]);
    }
    return $user;
  }

  public function updateFromCognitoUser(CognitoUser $cognitoUser): User
  {
    if ($this->cognito_id !== $cognitoUser->id) {
      abort(500, 'Something wrong when updating from cognito user');
    }

    $this->name           = $cognitoUser->username;
    $this->email          = $cognitoUser->email;
    $this->given_name     = $cognitoUser->given_name;
    $this->family_name    = $cognitoUser->family_name;
    $this->full_name      = $cognitoUser->full_name;
    $this->phone_number   = $cognitoUser->phone_number;
    $this->country_code   = $cognitoUser->country_code;
    $this->language_code  = $cognitoUser->language_code;
    $this->timezone       = $cognitoUser->timezone;
    $this->save();

    return $this;
  }

  public function isNewCustomer(): bool
  {
    return ($this->subscriptions()
      ->where('subscription_level', '>', 1)
      ->where('current_period', '>', 0)
      ->count() <= 0);
  }

  public function getDraftSubscriptionById(int $id): Subscription|null
  {
    return $this->subscriptions()
      ->where('status', Subscription::STATUS_DRAFT)
      ->find($id);
  }

  public function getActiveSubscription(): Subscription|null
  {
    return $this->subscriptions()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->first();
  }

  public function getActivePaidSubscription(): Subscription|null
  {
    return $this->subscriptions()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where('subscription_level', '>', 1)
      ->first();
  }

  public function getActiveLiveSubscription(): Subscription|null
  {
    return $this->subscriptions()
      ->where('status', Subscription::STATUS_ACTIVE)
      ->whereNot('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->where('subscription_level', '>', 1)
      ->first();
  }

  public function getPendingSubscription(): Subscription|null
  {
    return $this->subscriptions()
      ->where('status', Subscription::STATUS_PENDING)
      ->where('subscription_level', '>', 1)
      ->first();
  }

  public function updateSubscriptionLevel()
  {
    // test code
    $changes = [
      'user_id'       => $this->id,
      'origin'        => ['subscription_level' => $this->subscription_level, 'license_count' => $this->license_count],
    ];

    $subscription = $this->getActiveSubscription();
    $machineCount = $this->machines()->count();

    // create basic subscription if required
    if (!$subscription && $machineCount > 0) {
      $subscription = Subscription::createBasicMachineSubscription($this);
    }

    // stop basic subscription if required
    if ($subscription?->subscription_level == 1 && $machineCount <= 0) {
      $subscription->stop(Subscription::STATUS_STOPPED, 'all machine detached');
      $subscription = null;
    }

    if ($subscription) {
      $this->subscription_level = $subscription->subscription_level;
      $this->license_count = ($machineCount ?: 1) * GeneralConfiguration::getMachineLicenseUnit();
    } else {
      $this->subscription_level = 0;
      $this->license_count = 0;
    }

    $changes['new'] = ['subscription_level' => $this->subscription_level, 'license_count' => $this->license_count];
    Log::info('USER_LOG: updateSubscriptionLevel: ', $changes);

    $this->save();
    return $this;
  }
}
