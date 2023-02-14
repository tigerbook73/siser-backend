<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CouponController extends SimpleController
{
  protected string $modelClass = Coupon::class;

  protected function getListRules()
  {
    return [
      'code'        => ['filled'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'code'                            => ['required', 'string', 'max:255', 'unique:coupons'],
      'description'                     => ['string', 'max:255'],
      'condition'                       => ['required', 'array'],
      'condition.new_customer_only'     => ['required', 'boolean'],
      'condition.new_subscription_only' => ['required', 'boolean'],
      'condition.upgrade_only'          => ['required', 'boolean'],
      'percentage_off'                  => ['required', 'decimal:0,2', 'between:0,100'],
      'period'                          => ['required', 'integer', 'between:0,12'],
      'start_date'                      => ['required', 'date'],
      'end_date'                        => ['required', 'after:start_date', 'after:today'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'code'                            => ['filled', 'string', 'max:255', Rule::unique('coupons')->ignore(request("id"))],
      'description'                     => ['string', 'max:255'],
      'condition'                       => ['filled', 'array'],
      'condition.new_customer_only'     => ['required_with:condition', 'boolean'],
      'condition.new_subscription_only' => ['required_with:condition', 'boolean'],
      'condition.upgrade_only'          => ['required_with:condition', 'boolean'],
      'percentage_off'                  => ['filled', 'decimal:0,2', 'between:0,100'],
      'period'                          => ['filled', 'integer', 'between:0,12'],
      'start_date'                      => ['filled', 'date'],
      'end_date'                        => ['filled', 'date', 'after:start_date', 'after:today'],
    ];
  }

  protected function getUpdateRulesForDraft()
  {
    return $this->getUpdateRules();
  }

  protected function getUpdateRulesForActive()
  {
    return [
      'description'                     => ['string', 'max:255'],
      'end_date'                        => ['filled', 'date', 'after:start_date'],
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

    $coupon = new Coupon($inputs);
    $coupon->status = 'draft';
    DB::transaction(
      fn () => $coupon->save()
    );
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
    if ($coupon->status === 'draft') {
      $rules = $this->getUpdateRulesForDraft();
    } else if ($coupon->status === 'active') {
      $rules = $this->getUpdateRulesForActive();
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

    DB::transaction(
      fn () => $coupon->save()
      // TODO: update all active subscriptions
    );
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
    if ($coupon->status !== "draft") {
      return response()->json(['message' => 'Only draft coupon can be deleted'], 400);
    }

    return DB::transaction(
      fn () => $coupon->delete()
    );
  }
}
