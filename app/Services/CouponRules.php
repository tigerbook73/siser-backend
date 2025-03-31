<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\User;

class CouponRules
{
  static public function couponApplicable(Coupon $coupon, Plan $plan, int $licenseQuantity, User $user): CouponValidateResult
  {
    // status
    if ($coupon->status != Coupon::STATUS_ACTIVE) {
      return new CouponValidateResult(
        applicable: false,
        result_code: CouponValidateResultCode::FAILED_NOT_APPLICABLE,
        result_text: 'Coupon is not active',
      );
    }

    // start date
    if (now() <= $coupon->start_date) {
      return new CouponValidateResult(
        applicable: false,
        result_code: CouponValidateResultCode::FAILED_NOT_APPLICABLE,
        result_text: 'Coupon is not started yet',
      );
    }

    // end date
    if (now() > $coupon->end_date) {
      return new CouponValidateResult(
        applicable: false,
        result_code: CouponValidateResultCode::FAILED_NOT_APPLICABLE,
        result_text: 'Coupon is expired',
      );
    }

    // same product
    if ($coupon->product_name != $plan->product_name) {
      return new CouponValidateResult(
        applicable: false,
        result_code: CouponValidateResultCode::FAILED_NOT_APPLICABLE,
        result_text: 'Coupon is not applicable for this purchase',
      );
    }

    // coupon and plan's interval not matched
    if ($coupon->interval != Coupon::INTERVAL_LONGTERM) {
      if ($coupon->interval != $plan->interval || $coupon->interval_size != $plan->interval_count) {
        return new CouponValidateResult(
          applicable: false,
          result_code: CouponValidateResultCode::FAILED_NOT_APPLICABLE,
          result_text: 'Coupon\'s interval is not matched with plan\'s',
        );
      }
    }

    if ($coupon->discount_type == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      // free-trial coupon can not be redeemed for multi-license purchase
      if ($licenseQuantity > 1) {
        return new CouponValidateResult(
          applicable: false,
          result_code: CouponValidateResultCode::FAILED_FREE_TRIAL_NOT_ALLOWED,
          result_text: 'Free-trial coupon is not applicable for multi-license purchase.',
        );
      }

      // customer can not redeem free-trial coupon for a second time
      if ($user->invoices()
        ->where('status', Invoice::STATUS_COMPLETED)
        ->where('coupon_info->discount_type', Coupon::DISCOUNT_TYPE_FREE_TRIAL)
        ->exists()
      ) {
        return new CouponValidateResult(
          applicable: false,
          result_code: CouponValidateResultCode::FAILED_FREE_TRIAL_MORE_THAN_ONCE,
          result_text: 'Each customer is eligible for only one free trial.'
        );
      }
    }

    // more rule here
    // ...

    return new CouponValidateResult(
      applicable: true,
      result_code: CouponValidateResultCode::SUCCESS,
      result_text: 'Success',
      coupon_info: $coupon->info(null)
    );
  }
}
