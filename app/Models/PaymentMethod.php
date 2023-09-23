<?php

namespace App\Models;

use App\Models\Base\PaymentMethod as BasePaymentMethod;
use DigitalRiver\ApiSdk\Model\Source;

class PaymentMethod extends BasePaymentMethod
{
  use TraitDrAttr;

  const DR_SOURCE_ID    = 'source_id';

  // payment method with display data
  const TYPE_CREDIT_CARD    = 'creditCard';
  const TYPE_GOOGLE_PAY     = 'googlePay';

  static protected $attributesOption = [
    'id'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'type'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'display_data'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'created_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function getDrSourceId(): string|null
  {
    return $this->getDrAttr(self::DR_SOURCE_ID);
  }

  public function setDrSourceId(string $drSourceId): self
  {
    return $this->setDrAttr(self::DR_SOURCE_ID, $drSourceId);
  }

  public function fillFromDrObject(Source $drSource): self
  {
    $this->type = $drSource->getType();
    $this->setDrSourceId($drSource->getId());
    if ($drSource->getType() == PaymentMethod::TYPE_CREDIT_CARD) {
      $this->display_data = [
        'brand'             => $drSource->getCreditCard()->getBrand(),
        'last_four_digits'  => $drSource->getCreditCard()->getLastFourDigits(),
        'expiration_year'   => $drSource->getCreditCard()->getExpirationYear(),
        'expiration_month'  => $drSource->getCreditCard()->getExpirationMonth(),
      ];
    } else if ($drSource->getType() == PaymentMethod::TYPE_GOOGLE_PAY) {
      $this->display_data = [
        'brand'             => $drSource->getGooglePay()->getBrand(),
        'last_four_digits'  => $drSource->getGooglePay()->getLastFourDigits(),
        'expiration_year'   => $drSource->getGooglePay()->getExpirationYear(),
        'expiration_month'  => $drSource->getGooglePay()->getExpirationMonth(),
      ];
    } else {
      $this->display_data = null;
    }
    return $this;
  }

  public function info()
  {
    return [
      'type'          => $this->type,
      'display_data'  => $this->display_data,
      'dr'            => $this->dr,
    ];
  }
}
