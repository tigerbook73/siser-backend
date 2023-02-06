<?php

namespace App\Models;

use App\Models\Base\BillingInfo as BaseBillingInfo;

class BillingInfo extends BaseBillingInfo
{
  static protected $attributesOption = [
    'id'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'user_id'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'first_name'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'last_name'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'phone'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'organization'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'email'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'address'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'tax_id'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'created_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];
}
