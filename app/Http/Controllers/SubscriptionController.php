<?php

namespace App\Http\Controllers;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\LicensePackage;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionRenewal;
use App\Models\TaxId;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\CouponRules;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\RefundRules;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends SimpleController
{
  protected string $modelClass = Subscription::class;

  public function __construct(public SubscriptionManager $manager)
  {
    parent::__construct();
  }

  protected function getListRules(array $inputs = []): array
  {
    return [
      'user_id'     => ['filled', 'integer'],
      'plan_id'     => ['filled'],
      'status'      => ['filled'],
      'sub_status'  => ['filled'],
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'plan_id'     => [
        'required',
        Rule::exists('plans', 'id')->where(fn($q) => $q->where('subscription_level', '>', 1)->where('status', 'active'))
      ],
      'coupon_id'   => [
        'filled',
        Rule::exists('coupons', 'id')->where(fn($q) => $q->where('end_date', '>=', today())->where('status', 'active'))
      ],
      'tax_id'   => [
        'filled',
        Rule::exists('tax_ids', 'id')->where(fn($q) => $q->whereNot('status', TaxId::STATUS_INVALID))
      ],
      'license_package_id' => [
        'filled',
        Rule::exists('license_packages', 'id')->where(fn($q) => $q->where('status', LicensePackage::STATUS_ACTIVE))
      ],
      'license_count' => ['required_with:license_package_id', 'integer', 'min:1', 'max:' . LicensePackage::MAX_COUNT],
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

  // GET /users/{id}/subscriptions
  public function listByUser(Request $request, $user_id)
  {
    $this->validateUser();
    $request->merge(['user_id' => $user_id]);

    return parent::list($request);
  }

  // GET /subscriptions
  // default implementation

  // GET /subscriptions/{id}
  // default implementation


  // GET /account/tax_rate
  public function taxRate(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'tax_id'   => ['filled', Rule::exists('tax_ids', 'id')->where(fn($q) => $q->whereNot('status', TaxId::STATUS_INVALID))],
    ]);

    /** @var TaxId|null @taxId */
    $taxId = isset($inputs['tax_id']) ? $this->user->tax_ids()->find($inputs['tax_id']) : null;
    if ((isset($inputs['tax_id']) && !$taxId) || ($taxId && $taxId->status == TaxID::STATUS_INVALID)) {
      return response()->json(['message' => 'Invalid tax id!'], 400);
    }

    // retrieve tax rate
    try {
      $taxRate = $this->manager->retrieveTaxRate($this->user, $taxId);
      return  response()->json(['tax_rate' => $taxRate]);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  // POST /account/subscriptions
  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);

    if ($this->user->type == User::TYPE_BLACKLISTED) {
      return response()->json(['message' => 'User are blocked, please contact our support team.'], 400);
    }

    /** @var Plan $plan */
    $plan = Plan::find($inputs['plan_id']);
    if ($plan->status !== 'active') {
      return response()->json(['message' => 'Invalid plan!'], 400);
    }

    /** @var Coupon|null $coupon */
    $coupon = isset($inputs['coupon_id']) ? Coupon::find($inputs['coupon_id']) : null;
    if ((isset($inputs['coupon_id']) && !$coupon) || ($coupon && $coupon->status !== 'active')) {
      return response()->json(['message' => 'Invalid coupon!'], 400);
    }

    if ($coupon) {
      $applicable = CouponRules::couponApplicable($coupon, $plan, $this->user);
      if (!$applicable['applicable']) {
        return response()->json(['message' => 'Coupone is not applicable!'], 400);
      }
    }

    /**
     * @var LicensePackage|null $licensePackage
     */
    $licensePackage = isset($inputs['license_package_id']) ? LicensePackage::where('status', LicensePackage::STATUS_ACTIVE)->find($inputs['license_package_id']) : null;
    if ((isset($inputs['license_package_id']) && !$licensePackage)) {
      return response()->json(['message' => 'Invalid license package!'], 400);
    }

    /** @var BillingInfo|null $billingInfo */
    $billingInfo = $this->user->billing_info()->first();
    if (!$billingInfo || !$billingInfo->address['postcode']) {
      return response()->json(['message' => 'Billing information is not configured!'], 400);
    }

    /** @var TaxId|null @taxId */
    $taxId = isset($inputs['tax_id']) ? $this->user->tax_ids()->find($inputs['tax_id']) : null;
    if ((isset($inputs['tax_id']) && !$taxId) || ($taxId && $taxId->status == TaxID::STATUS_INVALID)) {
      return response()->json(['message' => 'Invalid tax id!'], 400);
    }

    /** @var Country|null $country */
    $country = Country::code($billingInfo->address['country'])->first();
    if (!$country) {
      return response()->json(['message' => 'Billing information is not configured!'], 400);
    }

    // create dr customer is required
    if (!$this->user->getDrCustomerId()) {
      $this->manager->createOrUpdateCustomer($billingInfo);
      $this->user->refresh();
    }

    $pendingSubscription = $this->user->getPendingSubscription();
    if ($pendingSubscription) {
      return response()->json(['message' => 'There is an pending subscription'], 400);
    }

    // creat subscription
    try {
      $subscription = $this->manager->createSubscription($this->user, $plan, $coupon, $taxId, $licensePackage, $inputs['license_count'] ?? 0);
      return  response()->json($this->transformSingleResource($subscription), 201);
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }


  public function destroy(int $id)
  {
    $this->validateUser();

    $draftSubscription = $this->user->subscriptions()->where('status', Subscription::STATUS_DRAFT)->find($id);
    if (!$draftSubscription) {
      return response()->json(['message' => 'Draft subscription not found!'], 404);
    }

    try {
      $this->manager->deleteSubscription($draftSubscription);
      return 1;
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  public function pay(Request $request, int $id)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'terms' => ['filled', 'string'],
    ]);

    if ($this->user->type == User::TYPE_BLACKLISTED) {
      return response()->json(['message' => 'User are blocked, please contact our support team.'], 400);
    }

    $draftSubscription = $this->user->getDraftSubscriptionById($id);
    if (!$draftSubscription) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    $pendingSubscription = $this->user->getPendingSubscription();
    if ($pendingSubscription) {
      return response()->json(['message' => 'There is an pending subscription'], 400);
    }

    $activeSubscription = $this->user->getActivePaidSubscription();
    if ($activeSubscription) {
      return response()->json(['message' => 'There is an active subscription'], 400);
    }

    /** @var PaymentMethod|null $paymentMethod */
    $paymentMethod = $this->user->payment_method;
    if (!$paymentMethod || !$paymentMethod->getDrSourceId()) {
      return response()->json(['message' => 'Payment method is not defined'], 400);
    }

    // pay subscription
    try {
      $subscription = $this->manager->paySubscription($draftSubscription, $paymentMethod, $inputs['terms'] ?? null);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  public function accountCancel(Request $request, int $id)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'refund'        => ['filled', 'bool'],
      'immediate'     => ['filled', 'bool'], // ignored for now
    ]);

    /** @var Subscription|null $activeSubscription */
    $activeSubscription = $this->user->getActivePaidSubscription();
    if (!$activeSubscription || $activeSubscription->id != $id) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    if ($activeSubscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
      return response()->json(['message' => 'Subscription is already on cancelling'], 422);
    }

    // check refundable
    if ($inputs['refund'] ?? false) {
      $result = RefundRules::customerRefundable($activeSubscription);
      if (!$result['refundable']) {
        return response()->json(['message' => $result['reason']], 400);
      }
    }

    // cancel subscription
    try {
      $subscription = $this->manager->cancelSubscription($activeSubscription, $inputs['refund'] ?? false, $inputs['immediate'] ?? false);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  public function refundable(int $id)
  {
    $this->validateUser();

    /** @var Subscription|null $subscription */
    $subscription = $this->user->subscriptions()->where('id', $id)->where('status', Subscription::STATUS_ACTIVE)->firstOrFail();

    $result = RefundRules::customerRefundable($subscription);

    $response = [
      'result' => $result['refundable'] ? 'refundable' : 'not_refundable',
      'reason' => $result['reason'] ?? '',
      'subscription' => $this->transformSingleResource($subscription),
      'invoice' => ($result['invoice'] ?? null)?->toResource('customer'),
    ];
    return response()->json($response);
  }

  public function confirmRenewal(int $id)
  {
    $this->validateUser();

    /** @var Subscription|null $activeSubscription */
    $activeSubscription = $this->user->getActivePaidSubscription();
    if (!$activeSubscription || $activeSubscription->id != $id) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    if ($activeSubscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
      return response()->json(['message' => 'Subscription is already on cancelling'], 422);
    }

    if (!$activeSubscription->renewal_info || $activeSubscription->renewal_info['status'] !== SubscriptionRenewal::STATUS_ACTIVE) {
      return response()->json(['message' => 'Subscription can not or need not to renew manually'], 400);
    }

    try {
      $subscription = $this->manager->renewSubscription($activeSubscription);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  /**
   * Cancel subscription by admin. No refund will be issued automatically. If required, admin shall issue refund manually.
   */
  public function cancel(Request $request, int $id)
  {
    $this->validateUser();

    /** @var Subscription|null $subscription */
    $subscription = Subscription::find($id);
    if (!$subscription || $subscription->id != $id) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    if ($subscription->getStatus() !== Subscription::STATUS_ACTIVE) {
      return response()->json(['message' => "Subscription in '{$subscription->getStatus()}' status can not be cancelled"], 400);
    }

    if ($subscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
      return response()->json(['message' => 'Subscription is already on cancelling'], 400);
    }

    // cancel subscription
    try {
      $subscription = $this->manager->cancelSubscription($subscription);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  public function stop(Request $request, int $id)
  {
    $this->validateUser();

    /** @var Subscription|null $subscription */
    $subscription = Subscription::find($id);
    if (!$subscription || $subscription->id != $id) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    // only active subscription and level > 1 can be stopped
    if ($subscription->sub_status !== Subscription::SUB_STATUS_CANCELLING) {
      return response()->json(['message' => 'Subscription not in cancelling status can not be stopped'], 400);
    }

    // stop subscription
    try {
      $this->manager->stopSubscription($subscription, 'stopped by admin');

      $subscription->sendNotification(SubscriptionNotification::NOTIF_TERMINATED);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }
}
