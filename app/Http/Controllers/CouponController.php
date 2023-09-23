<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Plan;
use App\Services\CouponRule;
use App\Services\CouponRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CouponController extends SimpleController
{
  protected string $modelClass = Coupon::class;

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
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'code'                            => ['required', 'string', 'max:255', 'unique:coupons'],
      'name'                            => ['required', 'string', 'max:255'],
      'product_name'                    => ['required', 'exists:products,name'],
      'type'                            => ['string', Rule::in([Coupon::TYPE_ONCE_OFF, Coupon::TYPE_SHARED])],
      'coupon_event'                    => ['string', 'max:255'],
      'discount_type'                   => ['string', Rule::in([Coupon::DISCOUNT_TYPE_FREE_TRIAL, Coupon::DISCOUNT_TYPE_PERCENTAGE])],
      'percentage_off'                  => ['required_if:discount_type,' . Coupon::DISCOUNT_TYPE_PERCENTAGE, 'decimal:0,2', 'between:0,100'],
      'interval'                        => ['required', 'string', Rule::in([Coupon::INTERVAL_DAY, Coupon::INTERVAL_WEEK, Coupon::INTERVAL_MONTH, Coupon::INTERVAL_YEAR])],
      'interval_count'                  => ['required', 'integer', 'between:0,12'],
      'condition'                       => ['required', 'array'],
      'condition.new_customer_only'     => ['required', 'boolean'],
      'condition.new_subscription_only' => ['required', 'boolean'],
      'condition.upgrade_only'          => ['required', 'boolean'],
      'start_date'                      => ['required', 'date'],
      'end_date'                        => ['required', 'after:start_date', 'after:today'],
      'status'                          => ['required', Rule::in([Coupon::STATUS_DRAFT, Coupon::STATUS_ACTIVE])],
    ];
  }

  protected function getUpdateRules(array $inputs = []): array
  {
    return [
      'code'                            => ['filled', 'string', 'max:255', Rule::unique('coupons')->ignore(request("id"))],
      'name'                            => ['filled', 'string', 'max:255'],
      'product_name'                    => ['filled', 'exists:products,name'],
      'type'                            => ['filled', 'string', Rule::in([Coupon::TYPE_ONCE_OFF, Coupon::TYPE_SHARED])],
      'coupon_event'                    => ['filled', 'string', 'max:255'],
      'discount_type'                   => ['filled', 'string', Rule::in([Coupon::DISCOUNT_TYPE_FREE_TRIAL, Coupon::DISCOUNT_TYPE_PERCENTAGE])],
      'percentage_off'                  => ['filled', 'decimal:0,2', 'between:0,100'],
      'interval'                        => ['filled', 'string', Rule::in([Coupon::INTERVAL_DAY, Coupon::INTERVAL_WEEK, Coupon::INTERVAL_MONTH, Coupon::INTERVAL_YEAR])],
      'interval_count'                  => ['filled', 'integer', 'between:0,12'],
      'condition'                       => ['filled', 'array'],
      'condition.new_customer_only'     => ['required_with:condition', 'boolean'],
      'condition.new_subscription_only' => ['required_with:condition', 'boolean'],
      'condition.upgrade_only'          => ['required_with:condition', 'boolean'],
      'start_date'                      => ['filled', 'date'],
      'end_date'                        => ['filled', 'date', 'after:start_date', 'after:today'],
      'status'                          => ['filled', Rule::in([Coupon::STATUS_DRAFT, Coupon::STATUS_ACTIVE, Coupon::STATUS_INACTIVE])],
    ];
  }

  protected function getUpdateRulesForDraft(array $inputs = [])
  {
    return $this->getUpdateRules($inputs);
  }

  protected function getUpdateRulesForActive(array $inputs = [])
  {
    return [
      'name'                            => ['string', 'max:255'],
      'coupon_event'                    => ['nullable', 'string', 'max:255'],
      'end_date'                        => ['filled', 'date', 'after:start_date'],
      'status'                          => ['filled', Rule::in([Coupon::STATUS_INACTIVE, Coupon::STATUS_ACTIVE])],
    ];
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

    // validate percentage type
    if ($inputs['discount_type'] == Coupon::DISCOUNT_TYPE_PERCENTAGE) {
      // validate percentage off
      if (empty($inputs['percentage_off'])) {
        return response()->json(['message' => 'percentage_off is required.'], 400);
      }
      if ($inputs['percentage_off'] >= 100) {
        return response()->json(['message' => 'percentage_off can not be 100.'], 400);
      }

      // validate interval
      if (
        $inputs['interval'] != Coupon::INTERVAL_MONTH &&
        $inputs['interval'] != Coupon::INTERVAL_YEAR
      ) {
        return response()->json(['message' => 'interval must be month or year.'], 400);
      }
    }

    // validate free-trial type
    if ($inputs['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
      if ($inputs['percentage_off'] != 100) {
        return response()->json(['message' => 'percentage_off must be 100.'], 400);
      }

      if ($inputs['interval_count'] == 0) {
        return response()->json(['message' => 'interval_count can not be 0.'], 400);
      }
    }

    // validate interval_count
    if ($inputs['interval'] == Coupon::INTERVAL_YEAR && $inputs['interval_count'] != 1) {
      return response()->json(['message' => 'interval_count must be 1 when interval is year.'], 400);
    }

    $coupon = new Coupon($inputs);
    $coupon->save();

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

    $inputs = $request->all();
    if ($coupon->status === Coupon::STATUS_DRAFT) {
      $rules = $this->getUpdateRulesForDraft($inputs);
    } else if ($coupon->status === Coupon::STATUS_ACTIVE) {
      $rules = $this->getUpdateRulesForActive($inputs);
    } else {
      return response()->json(['message' => "Coupon in {$coupon->status} status can not be updated"], 400);
    }
    $inputs = $this->validateRules($inputs, $rules);
    if (empty($inputs)) {
      abort(400, 'input data can not be empty.');
    }

    // validate and update attributers
    $updatable = $this->modelClass::getUpdatable($this->userType);
    foreach ($inputs as $attr => $value) {
      if (!in_array($attr, $updatable)) {
        abort(400, 'attribute: [' . $attr . '] is not updatable.');
      }
      $coupon->$attr = $value;
    }
    $coupon->save();
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
    if ($coupon->status !== Coupon::STATUS_DRAFT) {
      return response()->json(['message' => 'Only draft coupon can be deleted'], 400);
    }

    return DB::transaction(
      fn () => $coupon->delete()
    );
  }

  /**
   * post /coupon-validate
   */
  public function check(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'code'    => ['required', 'string', Rule::exists('coupons', 'code')->where(fn ($q) => $q->where('status', Coupon::STATUS_ACTIVE))],
      'plan_id' => ['filled', 'numeric', Rule::exists('plans', 'id')->where(fn ($q) => $q->where('status', Coupon::STATUS_ACTIVE)->where('subscription_level', '>', 1))],
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
