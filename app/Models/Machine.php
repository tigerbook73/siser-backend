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
    $this->user->updateSubscriptionLevel();
  }

  protected function afterDelete()
  {
    $this->user->updateSubscriptionLevel();
  }

  public function transfer(int $newUserId)
  {
    DB::transaction(function () use ($newUserId) {
      $prevUser = $this->user;
      $this->user_id = $newUserId;
      $this->save();

      $prevUser->updateSubscriptionLevel();
      $this->unsetRelation('user');
      $this->user->updateSubscriptionLevel();
    });

    return $this;
  }
}
