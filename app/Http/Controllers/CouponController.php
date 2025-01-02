<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponEvent;
use App\Models\Plan;
use App\Services\CouponRules;
use App\Services\Paddle\DiscountService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends SimpleController
{
  protected string $modelClass = Coupon::class;

  protected string $orderDirection = 'desc';

  public function __construct(public DiscountService $discountService)
  {
    parent::__construct();
  }

  protected function getListRules(array $inputs = []): array
  {
    return [
      'code'            => ['filled'],
      'name'            => ['filled'],
      'product_name'    => ['filled'],
      'type'            => ['filled'],
      'coupon_event'    => ['filled'],
      'discount_type'   => ['filled'],
      'interval'        => ['filled'],
      'status'          => ['filled'],
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'code'                            => ['required', 'string', 'max:16', 'regex:/^[a-zA-Z0-9]+$/', 'unique:coupons'],
      'name'                            => ['required', 'string', 'max:255'],
      'product_name'                    => ['required', 'exists:products,name'],
      'type'                            => ['required', 'string', Rule::in([Coupon::TYPE_ONCE_OFF, Coupon::TYPE_SHARED])],
      'coupon_event'                    => ['required', 'string', 'max:255'],
      'discount_type'                   => ['required', 'string', Rule::in([Coupon::DISCOUNT_TYPE_FREE_TRIAL, Coupon::DISCOUNT_TYPE_PERCENTAGE])],
      'percentage_off'                  => ['required_if:discount_type,' . Coupon::DISCOUNT_TYPE_PERCENTAGE, 'decimal:0,2', 'between:0,100'],
      'interval'                        => ['required', 'string', Rule::in([Coupon::INTERVAL_DAY, Coupon::INTERVAL_WEEK, Coupon::INTERVAL_MONTH, Coupon::INTERVAL_YEAR, Coupon::INTERVAL_LONGTERM])],
      'interval_count'                  => ['required', 'integer', 'between:0,12'],
      'condition'                       => ['nullable', 'array'], // for compatibility
      'start_date'                      => ['required', 'date'],
      'end_date'                        => ['required', 'after:start_date', 'after:today'],
      'status'                          => ['required', Rule::in([Coupon::STATUS_DRAFT, Coupon::STATUS_ACTIVE])],
    ];
  }

  protected function getUpdateRules(array $inputs = []): array
  {
    return [
      'code'                            => ['filled', 'string', 'max:16', 'regex:/^[a-zA-Z0-9]+$/', Rule::unique('coupons')->ignore(request("id"))],
      'name'                            => ['filled', 'string', 'max:255'],
      'product_name'                    => ['filled', 'exists:products,name'],
      'type'                            => ['filled', 'string', Rule::in([Coupon::TYPE_ONCE_OFF, Coupon::TYPE_SHARED])],
      'coupon_event'                    => ['filled', 'string', 'max:255'],
      'discount_type'                   => ['filled', 'string', Rule::in([Coupon::DISCOUNT_TYPE_FREE_TRIAL, Coupon::DISCOUNT_TYPE_PERCENTAGE])],
      'percentage_off'                  => ['filled', 'decimal:0,2', 'between:0,100'],
      'interval'                        => ['filled', 'string', Rule::in([Coupon::INTERVAL_DAY, Coupon::INTERVAL_WEEK, Coupon::INTERVAL_MONTH, Coupon::INTERVAL_YEAR, Coupon::INTERVAL_LONGTERM])],
      'interval_count'                  => ['filled', 'integer', 'between:0,12'],
      'condition'                       => ['nullable', 'array'], // for compatibility
      'start_date'                      => ['filled', 'date'],
      'end_date'                        => ['filled', 'date', 'after:start_date', 'after:today'],
      'status'                          => ['filled', Rule::in([Coupon::STATUS_DRAFT, Coupon::STATUS_ACTIVE, Coupon::STATUS_INACTIVE])],
    ];
  }

  /**
   * besides standard validate, we also need to validate more
   */
  public function validateMore(array $inputs): void
  {
    // validate percentage type
    if ($inputs['discount_type'] == Coupon::DISCOUNT_TYPE_PERCENTAGE) {
      // validate percentage off
      if (empty($inputs['percentage_off'])) {
        abort(response()->json(['message' => 'percentage_off is required.'], 400));
      }
      if ($inputs['percentage_off'] >= 100) {
        abort(response()->json(['message' => 'percentage_off can not be 100.'], 400));
      }

      // validate interval
      if (
        $inputs['interval'] != Coupon::INTERVAL_MONTH &&
        $inputs['interval'] != Coupon::INTERVAL_YEAR &&
        $inputs['interval'] != Coupon::INTERVAL_LONGTERM
      ) {
        abort(response()->json(['message' => 'interval must be month, year or longterm.'], 400));
      }

      // validate interval count
      if ($inputs['interval'] == Coupon::INTERVAL_LONGTERM && $inputs['interval_count'] != 0) {
        abort(response()->json(['message' => 'interval_count must be 0 for longterm coupon.'], 400));
      }
    }

    // validate free-trial type
    if ($inputs['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      // validate percentage off
      if ($inputs['percentage_off'] != 100) {
        abort(response()->json(['message' => 'percentage_off must be 100.'], 400));
      }

      // validate interval and interval_count
      if ($inputs['interval'] == Coupon::INTERVAL_LONGTERM || $inputs['interval_count'] == 0) {
        abort(response()->json(['message' => 'free_trial can not be longterm.'], 400));
      }
    }
  }

  /**
   * GET /coupons
   */
  // default

  /**
   * GET /coupons/{id}
   */
  // default

  /**
   * post /coupons
   */
  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);
    $this->validateMore($inputs);

    $coupon = new Coupon($inputs);
    $coupon->save();

    $this->discountService->createPaddleDiscount($coupon);

    return  response()->json($this->transformSingleResource($coupon), 201);
  }

  /**
   * patch /coupon/{id}
   */
  public function update(Request $request, int $id)
  {
    $this->validateUser();

    /** @var Coupon $coupon */
    $coupon = $this->baseQuery()->findOrFail($id);
    $inputs = $this->validateUpdate($request, $id);
    $coupon->forceFill($inputs);
    $this->validateMore($coupon->toArray());

    $coupon->save();

    if ($coupon->wasChanged()) {
      $this->discountService->createOrUpdatePaddleDiscount($coupon);
    }

    return $this->transformSingleResource($coupon->unsetRelations());
  }

  /**
   * delete /coupons/{id}
   */
  public function destroy(int $id)
  {
    $this->validateUser();

    /** @var Coupon $coupon */
    $coupon = $this->baseQuery()->findOrFail($id);
    if ($coupon->subscriptions()->count() > 0) {
      return response()->json(['message' => 'Coupon has been used, can not be deleted'], 400);
    }

    $coupon->status = Coupon::STATUS_INACTIVE;
    $coupon->save();

    // delete paddle discount
    $this->discountService->updatePaddleDiscount($coupon);

    $coupon->delete();

    CouponEvent::deleteNotUsed();
  }

  /**
   * post /coupon-validate
   */
  public function check(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'code'    => ['required', 'string', Rule::exists('coupons', 'code')->where(fn($q) => $q->where('status', Coupon::STATUS_ACTIVE))],
      'plan_id' => ['filled', 'numeric', Rule::exists('plans', 'id')->where(fn($q) => $q->where('status', Coupon::STATUS_ACTIVE)->where('subscription_level', '>', 1))],
      'user_id' => ['filled', 'exists:users,id'],
    ]);

    /** @var Coupon $coupon */
    $coupon = $this->baseQuery()->where('code', $inputs['code'])->firstOrFail();

    /** @var Plan|null $plan */
    $plan = isset($inputs['plan_id']) ? Plan::find($inputs['plan_id']) : null;

    $applyResult = CouponRules::couponApplicable($coupon, $plan, $this->user);
    if ($applyResult['applicable']) {
      return $coupon->info();
    }

    return response()->json(['message' => $applyResult['reason']], 400);
  }
}
