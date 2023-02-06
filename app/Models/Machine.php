<?php

namespace App\Models;

use App\Models\Base\Machine as BaseMachine;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Machine extends BaseMachine
{
  static protected $attributesOption = [
    'id'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'serial_no'   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'model'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'nickname'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
  ];


  protected function afterCreate()
  {
    $this->attachUser($this->user);
  }

  protected function afterDelete()
  {
    $this->detachUser($this->user);
  }

  public function transfer(int $newUserId)
  {
    DB::transaction(function () use ($newUserId) {
      $prevUser = $this->user;
      $this->user_id = $newUserId;
      $this->save();

      $this->detachUser($prevUser);
      $this->attachUser(User::find($newUserId));
    });

    return $this;
  }

  protected function attachUser(User $user)
  {
    // create subscription if required and update license count for user
    if (
      $user->subscriptions()
      ->whereIn('status', ['active'])
      ->whereRelation('plan', 'catagory', 'machine')
      ->count() <= 0
    ) {
      Subscription::createBasicMachineSubscription($user);

      $user->subscription_level = 1;
      $user->license_count = GeneralConfiguration::getMachineLicenseUnit();
    } else {
      $user->license_count += GeneralConfiguration::getMachineLicenseUnit();
    }

    $user->save();
  }

  protected function detachUser(User $user)
  {
    /** @var Subscription|null $subscription */
    $subscription = $user->subscriptions()
      ->whereIn('status', ['active'])
      ->whereRelation('plan', 'catagory', 'machine')
      ->first();
    if ($subscription) {
      $user->license_count -= GeneralConfiguration::getMachineLicenseUnit();
      if ($user->license_count <= 0) {
        // TODO: more to be considered if PRO plan support (e.g. when to stop)
        $subscription->end_date = today();
        $subscription->status = 'inactive';
        $subscription->save();

        // refresh user
        $user->subscription_level = 0;
        $user->license_count = 0;
      }
      $user->save();
    }
  }
}
