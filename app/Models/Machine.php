<?php

namespace App\Models;

use App\Models\Base\Machine as BaseMachine;

class Machine extends BaseMachine
{
  static protected $attributesOption = [
    'id'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'serial_no'   => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'model'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'manufacture' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'user_id'     => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
  ];


  protected function afterCreate()
  {
    /** @var User $user */
    $user = User::find($this->user_id);

    // create subscription if required and update license count for user
    if ($user->subscriptions()->count() <= 0) {
      Subscription::create([
        'user_id'     => $user->id,
        'plan_id'     => config('siser.plan.default_machine_plan'),
        'currency'    => 'USD',
        'price'       => 0.0,
        'start_date'  => today(),
        'end_date'    => null,
        'status'      => 'active',
      ]);

      $user->subscription_level = 1;
      $user->license_count = GeneralConfiguration::getMachineLicenseUnit();
    } else {
      $user->license_count += GeneralConfiguration::getMachineLicenseUnit();
    }

    $user->save();
  }
}
