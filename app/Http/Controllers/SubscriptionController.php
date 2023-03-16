<?php

namespace App\Http\Controllers;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends SimpleController
{
  protected string $modelClass = Subscription::class;

  protected function getListRules()
  {
    return [
      'user_id'     => ['filled'],
      'plan_id'     => ['filled'],
      'status'      => ['filled'],
      'sub_status'  => ['filled'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'plan_id'     => [
        'required',
        Rule::exists('plans', 'id')->where(fn ($q) => $q->where('subscription_level', '>', 1)->where('status', 'active'))
      ],
      'coupon_id'   => [
        'filled',
        Rule::exists('coupons', 'id')->where(fn ($q) => $q->where('end_date', '>=', today())->where('status', 'active'))
      ],
    ];
  }

  // GET account/subscriptions
  public function accountList(Request $request)
  {
    $this->validateUser();
    $request->merge(['user_id' => $this->user->id]);

    return parent::list($request);
  }

  // GET account/subscriptions/{id}
  public function accountIndex(int $id)
  {
    $this->validateUser();
    $object = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->transformSingleResource($object);
  }

  // GET /subscriptions
  // default implementation

  // GET /subscriptions/{id}
  // default implementation


  // POST /account/subscriptions
  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);

    /** @var Plan $plan */
    $plan = Plan::find($inputs['plan_id']);
    if ($plan->status !== 'active') {
      return response()->json(['message' => 'Invalid plan!'], 400);
    }

    /** @var Coupon|null $coupon */
    $coupon = isset($inputs['coupon_id']) ? Coupon::find($inputs['coupon_id']) : null;
    if ($coupon && $coupon->status !== 'active') {
      return response()->json(['message' => 'Invalid coupon!'], 400);
    }
    if ($coupon && !$coupon->validate($this->user->isNewCustomer())) {
      return response()->json(['message' => 'Coupone is not applicable!'], 400);
    }
    $couponDiscount = $coupon->percentage_off ?? 0;

    /** @var BillingInfo|null $billingInfo */
    $billingInfo = $this->user->billing_info()->first();
    if (!$billingInfo || !$billingInfo->address['postcode']) {
      return response()->json(['message' => 'Billing information is not configured!'], 400);
    }

    /** @var Country|null $country */
    $country = Country::code($billingInfo->address['country'])->first();
    if (!$country) {
      return response()->json(['message' => 'Billing information is not configured!'], 400);
    }

    $publicPlan = $plan->toPublicPlan($country->code);

    /** @var Subscription|null $pendingSubscription */
    $pendingSubscription = $this->user->subscriptions()->where('status', 'pending')->first();
    if ($pendingSubscription) {
      return response()->json(['message' => 'There is an pending subscription'], 400);
    }

    // TODO: create dr customer if not exist
    if (empty($this->user->dr['customer_id'])) {
      $customer_id = 'dr-customer-id-' . $this->user->id;
      $this->user->dr = ['customer_id' => $customer_id];
      $this->user->save();
    }

    // TODO: create checkout
    $checkoutId = 'dr-checkout-id-' . rand(0, 99999999);
    $checkoutPaymentSessionId = 'dr-session-id-' . rand(0, 99999999);

    // create subscription
    $subscription = new Subscription();
    $subscription->user_id                    = $this->user->id;
    $subscription->plan_id                    = $publicPlan['id'];
    $subscription->coupon_id                  = $coupon ? $coupon->id : null;
    $subscription->billing_info               = $billingInfo->toResource('customer');
    $subscription->plan_info                  = $publicPlan;
    $subscription->coupon_info                = $coupon ? $coupon->toResource('customer') : null;
    $subscription->processing_fee_info        = [
      'processing_fee_rate'     => $country->processing_fee_rate,
      'explicit_processing_fee' => $country->explicit_processing_fee,
    ];
    $subscription->currency                   = $country->currency;
    if ($country->explicit_processing_fee) {
      $subscription->price                    = round($publicPlan['price']['price'] * (1 -  $couponDiscount / 100), 2);
      $subscription->processing_fee           = round($subscription->price * $country->processing_fee_rate / 100, 2);
    } else {
      $subscription->price                    = round($publicPlan['price']['price'] * (1 -  $couponDiscount / 100) * (1 + $country->processing_fee_rate / 100), 2);
      $subscription->processing_fee           = 0;
    }
    $subscription->tax                        = round(($subscription->price + $subscription->processing_fee) * 0.1, 2); // TODO: from checkout
    $subscription->start_date                 = null;
    $subscription->end_date                   = null;
    $subscription->subscription_level         = $publicPlan['subscription_level'];
    $subscription->current_period             = 0;
    $subscription->current_period_start_date  = null;
    $subscription->current_period_end_date    = null;
    $subscription->next_invoice_date          = null;
    $subscription->dr                         = [
      'checkout_id'                           => $checkoutId,
      'checkout_payment_session_id'           => $checkoutPaymentSessionId
    ];
    $subscription->stop_reason                = null;
    $subscription->status                     = 'draft';
    $subscription->sub_status                 = 'normal';

    $subscription->save();

    return  response()->json($this->transformSingleResource($subscription), 201);
  }


  public function destroy(int $id)
  {
    $this->validateUser();

    $draftSubscription = $this->user->subscriptions()->where('status', 'draft')->find($id);
    if (!$draftSubscription) {
      return response()->json(['message' => 'Draft subscription not found!'], 404);
    }

    // TODO: delete DR checkout

    $draftSubscription->delete();
    return 1;
  }

  public function pay(int $id)
  {
    $this->validateUser();

    /** @var Subscription|null $draftSubscription */
    $draftSubscription = $this->user->subscriptions()->where('status', 'draft')->find($id);
    if (!$draftSubscription) {
      return response()->json(['message' => 'Subscripiton not found'], 404);
    }

    /** @var Subscription|null $pendingSubscription */
    $pendingSubscription = $this->user->subscriptions()->where('status', 'pending')->first();
    if ($pendingSubscription) {
      return response()->json(['message' => 'There is an pending subscription'], 400);
    }

    /** @var PaymentMethod|null $paymentMethod */
    $paymentMethod = $this->user->payment_method;
    if (!$paymentMethod || !$paymentMethod->dr['source_id']) {
      return response()->json(['message' => 'Payment method is not defined'], 400);
    }

    // TODO: attach source_id to checkout
    // POST /checkouts/{id}  {source_id}

    // TODO: convert checkout to order
    // POST /orders {checkoutId}
    $order_id = 'dr-order-id' . rand(0, 99999999);
    $subscription_id = 'dr-subscription-id' . rand(0, 99999999);

    // TODO: if return 
    $draftSubscription->dr = [
      'checkout_id' => $draftSubscription->dr['checkout_id'],
      'checkout_payment_session_id' => $draftSubscription->dr['checkout_payment_session_id'],
      'order_id' => $order_id,
      'subscription_id' => $subscription_id,
    ];
    $draftSubscription->status = 'pending';
    $draftSubscription->save();

    return  response()->json($this->transformSingleResource($draftSubscription));
  }

  public function cancel(int $id)
  {
    $this->validateUser();

    /** @var Subscription|null $activeSubscription */
    $activeSubscription = $this->user->subscriptions()->where('status', 'active')->find($id);
    if (!$activeSubscription) {
      return response()->json(['message' => 'Subscripiton not found'], 404);
    }

    if ($activeSubscription->sub_status === 'cancelling') {
      return response()->json(['message' => 'Subscripiton is already on cancelling'], 422);
    }

    // TODO: cancel subscription
    // POST /subscriptions/{id} {"state": "cancelled"}
    $activeSubscription->sub_status = 'cancelling';
    // TODO: update other attributes
    $activeSubscription->save();
  }

  // TODO:
  public function webhookOrderAccepted(Request $request)
  {
    // fulfill
  }

  public function webhookOrderCompleted(Request $request)
  {
    // active subscription
    // update subscription
  }

  public function webhookSubscriptionExtended(Request $request)
  {
    // fulfill
  }

  public function webhookSubscriptionFailed(Request $request)
  {
    // active subscription
    // update subscription
  }
}
