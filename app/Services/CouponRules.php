<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CouponRules
{
  static public function couponApplicable(Coupon $coupon, Plan $plan, User $user = null): array
  {
    // status
    if ($coupon->status != Coupon::STATUS_ACTIVE) {
      return ['applicable' => false, 'reason' => 'coupon is not active'];
    }

    // start date
    if (now() <= $coupon->start_date) {
      return ['applicable' => false, 'reason' => 'coupon is not started yet'];
    }

    // end date
    if (now() > $coupon->end_date) {
      return ['applicable' => false, 'reason' => 'coupon is expired'];
    }

    // same product
    if ($coupon->product_name != $plan->product_name) {
      return ['applicable' => false, 'reason' => 'coupon product and plan product do not matched'];
    }

    // for percentage off coupon, the interval shall be same with plan's interval and interval_count should be a multiple of the plan's interval_count
    // coupont->interveal
    if (
      $coupon->discount_type == Coupon::DISCOUNT_TYPE_PERCENTAGE &&
      ($coupon->interval != $plan->interval || $coupon->interval_count % $plan->interval_count != 0)
    ) {
      return ['applicable' => false, 'reason' => 'coupon\'s interval and plan\'s interval do not matched'];
    }

    // addition condition
    $condition = $coupon->getCondition();

    // countries
    if ($condition['countries'] && count($condition['countries']) > 0) {
      $country = $user?->billing_info->address['country'];
      if (!in_array($country, $condition['countries'])) {
        return ['applicable' => false, 'reason' => 'coupon is not applicable for this country'];
      }
    }

    // more rule here
    // ...

    return ['applicable' => true, 'coupon' => $coupon->info()];
  }
}
