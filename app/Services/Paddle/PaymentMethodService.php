<?php

namespace App\Services\Paddle;

use App\Models\PaddleMap;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\DigitalRiver\SubscriptionManagerResult;
use Paddle\SDK\Entities\Shared\TransactionPaymentAttempt;
use Paddle\SDK\Notifications\Entities\Shared\TransactionPaymentAttempt as NotificationTransactionPaymentAttempt;
use Paddle\SDK\Notifications\Events\PaymentMethodDeleted;

class PaymentMethodService extends PaddleEntityService
{
  const TYPE_MAP = [
    'alipay'      => PaymentMethod::TYPE_ALIPAY,
    'apple_pay'   => PaymentMethod::TYPE_APPLE_PAY,
    'card'        => PaymentMethod::TYPE_CREDIT_CARD,
    'google_pay'  => PaymentMethod::TYPE_GOOGLE_PAY,
    'paypal'      => PaymentMethod::TYPE_PAYPAL,

  ];

  public function createOrUpdatePaymentMethod(User $user, TransactionPaymentAttempt|NotificationTransactionPaymentAttempt $paymentAttempt): PaymentMethod
  {
    $paymentMethod = $user->payment_method ?? new PaymentMethod(['user_id' => $user->id]);
    return $this->updatePaymentMethodFromAttempt($paymentMethod, $paymentAttempt);
  }

  protected function updatePaymentMethodFromAttempt(PaymentMethod $paymentMethod, TransactionPaymentAttempt|NotificationTransactionPaymentAttempt $paymentAttempt): PaymentMethod
  {
    $paymentMethod->type  = self::TYPE_MAP[$paymentAttempt->methodDetails->type->getValue()] ?? PaymentMethod::TYPE_UNKNOWN;
    $paymentMethod->dr    = [];

    $paddleCard = $paymentAttempt->methodDetails->card;
    $paymentMethod->display_data = $paddleCard ? [
      'brand'             => $paddleCard->type ?? "",
      'last_four_digits'  => $paddleCard->last4 ?? "",
      'expiration_year'   => $paddleCard->expiryYear,
      'expiration_month'  => $paddleCard->expiryMonth,
    ] : [];
    $paymentMethod->setMetaPaddlePaymentMethodId($paymentAttempt->paymentMethodId);
    $paymentMethod->save();

    PaddleMap::createOrUpdate($paymentAttempt->paymentMethodId, PaymentMethod::class, $paymentMethod->id);
    return $paymentMethod;
  }


  public function onPaymentMethodDeleted(PaymentMethodDeleted $paymentMethodDeleted)
  {
    $paddlePaymentMethod = $paymentMethodDeleted->paymentMethod;

    $billingInfo = PaddleMap::findBillingInfoByPaddleId($paddlePaymentMethod->customerId);
    if (!$billingInfo) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('BillingInfo not found for customer_id: ' . $paddlePaymentMethod->customerId);
      return;
    }

    $paymentMethod = $billingInfo->user->payment_method;
    if (!$paymentMethod) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('PaymentMethod not found for user_id: ' . $billingInfo->user_id);
      return;
    }
    if ($paymentMethod->getMeta()->paddle->payment_method_id !== $paddlePaymentMethod->id) {
      $this->result
        ->setResult(SubscriptionManagerResult::RESULT_SKIPPED)
        ->appendMessage('Deleted payment method is not the active one: ' . $paddlePaymentMethod->id);
      return;
    }

    $paymentMethod->delete();
  }
}
