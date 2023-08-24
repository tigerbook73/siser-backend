<?php

namespace App\Models;

trait TraitPaymentMethodInfo
{
  public function getPaymentMethodInfo(): PaymentMethodInfo
  {
    return PaymentMethodInfo::fromArray($this->payment_method_info);
  }

  public function setPaymentMethodInfo(PaymentMethod $paymentMethodInfo)
  {
    $this->payment_method_info = (array)$paymentMethodInfo->info();
  }
}
