<?php

namespace App\Models;

use App\Models\Base\BillingInfo as BaseBillingInfo;
use App\Services\Locale;

class BillingInfo extends BaseBillingInfo
{
  public const CUSTOMER_TYPE_INDIVIDUAL   = 'individual';
  public const CUSTOMER_TYPE_BUSINESS     = 'business';

  static protected $attributesOption = [
    'id'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'user_id'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'first_name'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'last_name'     => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'phone'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'customer_type' => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'organization'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'email'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'address'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'language'      => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'locale'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'tax_id'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'created_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function beforeCreate()
  {
    $this->language = Locale::defaultLanguage($this->address['country'], $this->language);
    $this->locale = Locale::locale($this->language, $this->address['country']);
  }

  public function beforeUpdate()
  {
    $this->locale = Locale::locale($this->language, $this->address['country']);
  }

  static public function createDefault(User $user): BillingInfo
  {
    $billingInfo = new BillingInfo([
      'user_id'       => $user->id,
      'first_name'    => $user->given_name,
      'last_name'     => $user->family_name,
      'phone'         => $user->phone_number,
      'customer_type' => self::CUSTOMER_TYPE_INDIVIDUAL,
      'organization'  => "",
      'email'         => $user->email,
      'address'       => [
        'line1'       => '',
        'line2'       => '',
        'city'        => '',
        'postcode'    => '',
        'state'       => '',
        'country'     => Country::findByCode($user->country_code ?? 'US')->code,
      ],
      'tax_id'        => null
    ]);
    $billingInfo->language  = Locale::defaultLanguage($user->country_code, $user->language_code);
    $billingInfo->locale    = Locale::locale($billingInfo->language, $billingInfo->address['country']);

    $billingInfo->id = $user->id;
    $billingInfo->save();
    return $billingInfo;
  }
}
