<?php

namespace App\Models;

use App\Models\Base\PaddleMap as BasePaddleMap;
use Illuminate\Database\Eloquent\Model;

/**
 * @template T of Model
 * @property T $model
 * @property mixed $meta
 */
class ModelWithMeta
{
  /**
   * @param T $model
   */
  public function __construct(
    public Model $model,
    public mixed $meta
  ) {}
}

class PaddleMap extends BasePaddleMap
{
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

  static public function findByPaddleId(string $paddleId, ?string $modelClass = null): ?self
  {
    $model = self::where('paddle_id', $paddleId)->first();
    if (
      !$model ||
      $modelClass && $model->model_class !== $modelClass
    ) {
      return null;
    }
    return $model;
  }

  /**
   * @template T of Model
   * @param ?class-string<T> $modelClass
   * @return ?T
   */
  static public function findModel(string $paddleId, ?string $modelClass = null): ?Model
  {
    $paddleMap = self::findByPaddleId($paddleId, $modelClass);
    return $paddleMap?->model_class::find($paddleMap->model_id);
  }

  /**
   * @template T of Model
   * @param class-string<T> $modelClass
   */
  static public function findModelWithMeta(string $paddleId, ?string $modelClass = null): ?ModelWithMeta
  {
    $paddleMap = self::findByPaddleId($paddleId, $modelClass);
    if (!$paddleMap) {
      return null;
    }
    $model = $paddleMap->model_class::find($paddleMap->model_id);
    return new ModelWithMeta($model, $paddleMap->meta);
  }

  static public function findBillingInfo(string $paddleId): ?BillingInfo
  {
    return self::findModel($paddleId, BillingInfo::class);
  }

  /**
   * @return ?ModelWithMeta<BillingInfo>
   */
  static public function findBillingInfoWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, BillingInfo::class);
  }

  static public function findCoupon(string $paddleId): ?Coupon
  {
    return self::findModel($paddleId, Coupon::class);
  }

  /**
   * @return ?ModelWithMeta<Coupon>
   */
  static public function findCouponWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, Coupon::class);
  }

  static public function findPaymentMethod(string $paddleId): ?PaymentMethod
  {
    return self::findModel($paddleId, PaymentMethod::class);
  }

  /**
   * @return ?ModelWithMeta<PaymentMethod>
   */
  static public function findPaymentMethodWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, PaymentMethod::class);
  }

  static public function findPlan(string $paddleId): ?Plan
  {
    return self::findModel($paddleId, Plan::class);
  }

  /**
   * @return ?ModelWithMeta<Plan>
   */
  static public function findPlanWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, Plan::class);
  }

  static public function findProduct(string $paddleId): ?Product
  {
    return self::findModel($paddleId, Product::class);
  }

  /**
   * @return ?ModelWithMeta<Product>
   */
  static public function findProductWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, Product::class);
  }

  static public function findSubscription(string $paddleId): ?Subscription
  {
    return self::findModel($paddleId, Subscription::class);
  }

  /**
   * @return ?ModelWithMeta<Subscription>
   */
  static public function findSubscriptionWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, Subscription::class);
  }

  static public function findInvoice(string $paddleId): ?Invoice
  {
    return self::findModel($paddleId, Invoice::class);
  }

  /**
   * @return ?ModelWithMeta<Invoice>
   */
  static public function findInvoiceWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, Invoice::class);
  }

  static public function findRefund(string $paddleId): ?Refund
  {
    return self::findModel($paddleId, Refund::class);
  }

  /**
   * @return ?ModelWithMeta<Refund>
   */
  static public function findRefundWithMeta(string $paddleId): ?ModelWithMeta
  {
    return self::findModelWithMeta($paddleId, Refund::class);
  }
}
