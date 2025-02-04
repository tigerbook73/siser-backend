<?php

namespace App\Models;

use App\Models\Base\PaymentMethod as BasePaymentMethod;

class PaymentMethod extends BasePaymentMethod
{
  // payment method with display data
  const TYPE_ALIPAY         = 'aliPay';
  const TYPE_APPLE_PAY      = 'applePay';
  const TYPE_CREDIT_CARD    = 'creditCard';
  const TYPE_GOOGLE_PAY     = 'googlePay';
  const TYPE_PAYPAL         = 'payPal';
  const TYPE_PAYPAL_BILLING = 'payPalBilling';
  const TYPE_UNKNOWN        = 'unknown';

  static protected $attributesOption = [
    'id'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'user_id'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'type'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'display_data'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'dr'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_1, 'listable' => 0b0_1_1],
    'meta'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_0, 'listable' => 0b0_1_1],
    'created_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'    => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function info()
  {
    return [
      'type'          => $this->type,
      'display_data'  => $this->display_data,
    ];
  }

  public function getMeta(): PaymentMethodMeta
  {
    return PaymentMethodMeta::from($this->meta);
  }

  public function setMeta(PaymentMethodMeta $meta): self
  {
    $this->meta = $meta->toArray();
    return $this;
  }

  public function setMetaPaddlePaymentMethodId(?string $paddlePaymentMethodId): self
  {
    $meta = $this->getMeta();
    $meta->paddle->payment_method_id = $paddlePaymentMethodId;
    return $this->setMeta($meta);
  }
}
