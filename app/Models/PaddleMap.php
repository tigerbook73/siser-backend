<?php

namespace App\Models;

use App\Models\Base\PaddleMap as BasePaddleMap;


class PaddleMap extends BasePaddleMap
{
  /**
   * @param string $paddleId  Paddle ID for paddle resource
   * @param string $model_class  Class name of model in our system
   * @param int $model_id  ID of model in our system
   */
  static public function createOrUpdate(string $paddleId, string $model_class, int $model_id, mixed $meta = null): self
  {
    return PaddleMap::updateOrCreate(
      ['paddle_id' => $paddleId],
      [
        'paddle_id'   => $paddleId,
        'model_class' => $model_class,
        'model_id'    => $model_id,
        'meta'        => $meta,
      ]
    );
  }

  /**
   * @param string $paddleId  Paddle ID for paddle resource
   */
  static public function findByPaddleId(string $paddleId): ?self
  {
    return self::where('paddle_id', $paddleId)->first();
  }

  static public function findMetaByPaddleId(string $paddleId): mixed
  {
    $paddleMap = self::findByPaddleId($paddleId);
    return $paddleMap?->meta;
  }

  /**
   * @param string $paddleId  Paddle ID for paddle resource
   * @param string|array<string> $modelClass  Class name(s) of model in our system
   * @return mixed|null  Model in our system
   */
  static public function findModelByPaddleId(string $paddleId, string|array $modelClass)
  {
    $paddleMap = self::findByPaddleId($paddleId);
    if (!$paddleMap) {
      return null;
    }
    $modelClasses = is_array($modelClass) ? $modelClass : [$modelClass];
    if (!in_array($paddleMap->model_class, $modelClasses)) {
      return null;
    }
    return $paddleMap->model_class::find($paddleMap->model_id);
  }

  /**
   * @param string $paddleId  Paddle ID for customer, address or business
   */
  static public function findBillingInfoByPaddleId(string $paddleId): ?BillingInfo
  {
    return self::findModelByPaddleId($paddleId, BillingInfo::class);
  }

  /**
   * @param string $paddleId  Paddle ID for discount
   */
  static public function findCouponByPaddleId(string $paddleId): ?Coupon
  {
    return self::findModelByPaddleId($paddleId, Coupon::class);
  }

  /**
   * @param string $paddleId  Paddle ID for payment method
   */
  static public function findPaymentMethodByPaddleId(string $paddleId): ?PaymentMethod
  {
    return self::findModelByPaddleId($paddleId, PaymentMethod::class);
  }

  /**
   * @param string $paddleId  Paddle ID for price
   */
  static public function findPlanByPaddleId(string $paddleId): ?Plan
  {
    return self::findModelByPaddleId($paddleId, Plan::class);
  }

  /**
   * @param string $paddleId  Paddle ID for license price
   */
  static public function findLicensePlanByPaddleId(string $paddleId): ?LicensePlan
  {
    return self::findModelByPaddleId($paddleId, LicensePlan::class);
  }

  /**
   * @param string $paddleId  Paddle ID for product
   */
  static public function findProductByPaddleId(string $paddleId): ?Product
  {
    return self::findModelByPaddleId($paddleId, Product::class);
  }

  /**
   * @param string $paddleId  Paddle ID for customer
   */
  static public function findSubscriptionByPaddleId(string $paddleId): ?Subscription
  {
    return self::findModelByPaddleId($paddleId, Subscription::class);
  }

  /**
   * @param string $paddleId  Paddle ID for customer
   */
  static public function findInvoiceByPaddleId(string $paddleId): ?Invoice
  {
    return self::findModelByPaddleId($paddleId, Invoice::class);
  }

  /**
   * @param string $paddleId  Paddle ID for refund
   */
  static public function findRefundByPaddleId(string $paddleId): ?Refund
  {
    return self::findModelByPaddleId($paddleId, Refund::class);
  }
}
