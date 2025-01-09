<?php

namespace App\Services\Paddle;

use App\Models\Coupon;
use App\Models\Paddle\DiscountCustomData;
use App\Models\PaddleMap;
use App\Models\Plan;
use App\Models\Product;
use Paddle\SDK\Entities\Discount;
use Paddle\SDK\Entities\Discount\DiscountStatus;
use Paddle\SDK\Entities\Discount\DiscountType;
use Paddle\SDK\Entities\Shared\CurrencyCode;
use Paddle\SDK\Exceptions\ApiError\DiscountApiError;
use Paddle\SDK\Resources\Discounts\Operations\CreateDiscount;
use Paddle\SDK\Resources\Discounts\Operations\UpdateDiscount;

class DiscountService extends PaddleEntityService
{
  /**
   * @param Coupon $coupon
   * @param string $mode create|update
   */
  public function prepareData(Coupon $coupon, string $mode): CreateDiscount|UpdateDiscount
  {
    if ($mode !== 'create' && $mode !== 'update') {
      throw new \Exception('Invalid mode');
    }

    $customData = DiscountCustomData::from([
      'coupon_id' => $coupon->id,
      'coupon_name' => $coupon->name,
      'coupon_event' => $coupon->coupon_event,
      'coupon_timestamp' => $coupon->updated_at->format('Y-m-d H:i:s'),
    ])->toCustomData();

    $percentageOff = ($coupon->discount_type == Coupon::DISCOUNT_TYPE_FREE_TRIAL) ? 100 : $coupon->percentage_off;

    // if coupon's interval is longterm or interval_count is 0, maximum recurring intervals is null (forever)
    $recurringIntervals = ($coupon->interval == Coupon::INTERVAL_LONGTERM || $coupon->interval_count == 0) ?
      null :
      $coupon->interval_count;

    // if coupon's interval is longterm, suitable for all product, else suitable for same interval product
    $restrictTo = null;
    if ($coupon->interval !== Coupon::INTERVAL_LONGTERM) {
      // all license package are allowd because they only work together with plans
      $paddleProductIds = Product::where('type', Product::TYPE_LICENSE_PACKAGE)
        ->get()
        ->map(fn($product) => $product->getMeta()->paddle->product_id)
        ->all();

      // get all plans with same product name and interval
      $paddlePriceIds = Plan::public()
        ->where('product_name', $coupon->product_name)
        ->where('interval', $coupon->interval)
        ->get()
        ->map(fn($plan) => $plan->getMeta()->paddle->price_id)
        ->all();
      $restrictTo = array_merge($paddleProductIds, $paddlePriceIds);
    }

    /** only alphanumeric characters are allowed */
    $cleanedCode = preg_replace('/[^a-zA-Z0-9]/', '', $coupon->code);
    $cleanedCode = substr($cleanedCode, 0, 16);

    /**
     * if coupon's type is once off, usage limit is 1
     * if coupon's type is shared, usage limit is null (unlimited)
     */
    $usageLimit = $coupon->type == Coupon::TYPE_ONCE_OFF ? 1 : null;

    if ($mode == 'create') {
      return new CreateDiscount(
        amount: (string)$percentageOff,
        description: $coupon->name,
        type: DiscountType::Percentage(),
        enabledForCheckout: true,
        recur: true,
        currencyCode: CurrencyCode::USD(),
        code: $cleanedCode,
        maximumRecurringIntervals: $recurringIntervals,
        usageLimit: $usageLimit,
        restrictTo: $restrictTo,
        expiresAt: $coupon->end_date->format('Y-m-d\TH:i:s\Z'),
        customData: $customData,
      );
    } else {
      return new UpdateDiscount(
        amount: (string)$percentageOff,
        description: $coupon->name,
        type: DiscountType::Percentage(),
        enabledForCheckout: true,
        recur: true,
        currencyCode: CurrencyCode::USD(),
        code: $cleanedCode,
        maximumRecurringIntervals: $recurringIntervals,
        usageLimit: $usageLimit,
        restrictTo: $restrictTo,
        expiresAt: $coupon->end_date->format('Y-m-d\TH:i:s\Z'),
        customData: $customData,
        status: $coupon->status == Coupon::STATUS_ACTIVE ? DiscountStatus::Active() : DiscountStatus::Archived(),
      );
    }
  }


  /**
   * create discount from coupon
   */
  public function createPaddleDiscount(Coupon $coupon): Discount
  {
    if ($coupon->status !== Coupon::STATUS_ACTIVE) {
      throw new \Exception('Coupon is not active');
    }

    $createDiscount = $this->prepareData($coupon, 'create');

    try {
      $paddleDiscount = $this->paddleService->createDiscount($createDiscount);
      $this->updateCoupon($coupon, $paddleDiscount);
      return $paddleDiscount;
    } catch (DiscountApiError $e) {
      // if discount already exists, update discount
      if ($e->errorCode === 'discount_code_conflict') {
        preg_match('/dsc_[a-zA-Z0-9]+/', $e->detail, $matches);
        $id = $matches[0] ?? null;
        if (!$id) {
          throw $e;
        }

        // prefill $coupon->meta->discount_id
        $coupon->setMetaPaddleDiscountId($id);
        $paddleDiscount = $this->updatePaddleDiscount($coupon);
        $this->updateCoupon($coupon, $paddleDiscount);
        return $paddleDiscount;
      } else {
        throw $e;
      }
    }
  }

  public function updatePaddleDiscount(Coupon $coupon): Discount
  {
    $meta = $coupon->getMeta();
    if (!$meta->paddle->discount_id) {
      throw new \Exception('Paddle discount not exist');
    }

    $updateDiscount = $this->prepareData($coupon, 'update');
    $paddleCoupon = $this->paddleService->updateDiscount($meta->paddle->discount_id, $updateDiscount);
    $this->updateCoupon($coupon, $paddleCoupon);
    return $paddleCoupon;
  }

  public function createOrUpdatePaddleDiscount(Coupon $coupon): Discount
  {
    return $coupon->getMeta()->paddle->discount_id ?
      $this->updatePaddleDiscount($coupon) :
      $this->createPaddleDiscount($coupon);
  }

  public function updateCoupon(Coupon $coupon, Discount $paddleDiscount): Coupon
  {
    $coupon->setMetaPaddleDiscountId($paddleDiscount->id)
      ->setMetaPaddleTimestamp($paddleDiscount->updatedAt->format('Y-m-d\TH:i:s\Z'))
      ->save();

    PaddleMap::createOrUpdate($paddleDiscount->id, Coupon::class, $coupon->id);
    return $coupon;
  }
}
