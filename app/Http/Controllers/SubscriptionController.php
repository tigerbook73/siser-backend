<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Http\Request;

class SubscriptionController extends SimpleController
{
  protected string $modelClass = Subscription::class;

  public function __construct(
    public SubscriptionManagerPaddle $paddleManager
  ) {
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

  // GET account/subscriptions
  public function accountList(Request $request)
  {
    $request->merge(['user_id' => auth('api')->id()]);
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
    $request->merge(['user_id' => $user_id]);
    return parent::list($request);
  }

  // GET /subscriptions
  // default implementation

  // GET /subscriptions/{id}
  // default implementation

  // public function accountCancel(Request $request, int $id)
  // {
  //   $this->validateUser();

  //   $inputs = $request->validate([
  //     'immediate'     => ['filled', 'bool'], // ignored for now
  //   ]);

  //   /** @var Subscription|null $activeSubscription */
  //   $activeSubscription = $this->user->getActivePaidSubscription();
  //   if (!$activeSubscription || $activeSubscription->id != $id) {
  //     return response()->json(['message' => 'Subscription not found'], 404);
  //   }

  //   if ($activeSubscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
  //     return response()->json(['message' => 'Subscription is already on cancelling'], 422);
  //   }

  //   // cancel subscription
  //   try {
  //     $subscription = $this->manager->cancelSubscription($activeSubscription, immediate: $inputs['immediate'] ?? false);
  //     return  response()->json($this->transformSingleResource($subscription));
  //   } catch (\Throwable $th) {
  //     return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
  //   }
  // }

  // public function accountRefundable(int $id)
  // {
  //   $this->validateUser();

  //   /** @var Subscription|null $subscription */
  //   $subscription = $this->user->subscriptions()
  //     ->where('id', $id)
  //     ->where('status', Subscription::STATUS_ACTIVE)
  //     ->firstOrFail();

  //   $result = RefundRules::subscriptionRefundable($subscription);
  //   $invoices = array_map(fn($invoice) => $invoice->invoice, $result->getInvoices());

  //   $response = [
  //     'result'            => $result->isRefundable() ? 'refundable' : 'not_refundable',
  //     'reason'            => $result->getReason(),
  //     'refundable_amount' => $result->getRefundableAmount(),
  //     'subscription'      => $this->transformSingleResource($subscription),
  //     'invoices'          => $this->transformMultipleResources($invoices),
  //   ];
  //   return response()->json($response);
  // }

  /**
   * Cancel subscription by admin. No refund will be issued automatically. If required, admin shall issue refund manually.
   */
  // public function cancel(Request $request, int $id)
  // {
  //   $this->validateUser();

  //   /** @var Subscription|null $subscription */
  //   $subscription = Subscription::find($id);
  //   if (!$subscription || $subscription->id != $id) {
  //     return response()->json(['message' => 'Subscription not found'], 404);
  //   }

  //   if ($subscription->getStatus() !== Subscription::STATUS_ACTIVE) {
  //     return response()->json(['message' => "Subscription in '{$subscription->getStatus()}' status can not be cancelled"], 400);
  //   }

  //   if ($subscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
  //     return response()->json(['message' => 'Subscription is already on cancelling'], 400);
  //   }

  //   // cancel subscription
  //   try {
  //     $subscription = $this->manager->cancelSubscription($subscription, immediate: false);
  //     return  response()->json($this->transformSingleResource($subscription));
  //   } catch (\Throwable $th) {
  //     return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
  //   }
  // }

  // public function stop(Request $request, int $id)
  // {
  //   $this->validateUser();

  //   /** @var Subscription|null $subscription */
  //   $subscription = Subscription::find($id);
  //   if (!$subscription || $subscription->id != $id) {
  //     return response()->json(['message' => 'Subscription not found'], 404);
  //   }

  //   // only active subscription and level > 1 can be stopped
  //   if ($subscription->sub_status !== Subscription::SUB_STATUS_CANCELLING) {
  //     return response()->json(['message' => 'Subscription not in cancelling status can not be stopped'], 400);
  //   }

  //   // stop subscription
  //   try {
  //     $this->manager->stopSubscription($subscription, 'stopped by admin');

  //     $subscription->sendNotification(SubscriptionNotification::NOTIF_TERMINATED);
  //     return  response()->json($this->transformSingleResource($subscription));
  //   } catch (\Throwable $th) {
  //     return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
  //   }
  // }

  // public function accountLicensePackageCancel(Request $request, int $id)
  // {
  //   $this->validateUser();

  //   $inputs = $request->validate([
  //     'immediate' => ['filled', 'boolean'],
  //   ]);

  //   $subscription = $this->user->subscriptions()->find($id);
  //   if (!$subscription) {
  //     return response()->json(['message' => 'Subscription not found'], 404);
  //   }

  //   try {
  //     $subscription = $this->manager->cancelLicensePackage($subscription, $inputs['immediate']);
  //     return  response()->json($this->transformSingleResource($subscription));
  //   } catch (\Throwable $th) {
  //     return response()->json(['message' => $th->getMessage()], 409);
  //   }
  // }

  // public function accountLicensePackageDecrease(Request $request, int $id)
  // {
  //   $this->validateUser();

  //   $inputs = $request->validate([
  //     'license_count' => ['required', 'integer'],
  //     'immediate' => ['filled', 'boolean'],
  //   ]);

  //   /** @var Subscription|null $subscription */
  //   $subscription = $this->user->subscriptions()->find($id);
  //   if (!$subscription) {
  //     return response()->json(['message' => 'Subscription not found'], 404);
  //   }

  //   try {
  //     $subscription = $this->manager->decreaseLicenseNumber($subscription, $inputs['license_count'], $inputs['immediate'] ?? false);
  //     return  response()->json($this->transformSingleResource($subscription));
  //   } catch (\Throwable $th) {
  //     return response()->json(['message' => $th->getMessage()], 409);
  //   }
  // }

  // public function accountLicensePackageRefundable(int $id)
  // {
  //   $this->validateUser();

  //   /** @var Subscription|null $subscription */
  //   $subscription = $this->user->subscriptions()
  //     ->where('id', $id)
  //     ->where('status', Subscription::STATUS_ACTIVE)
  //     ->firstOrFail();

  //   $result = RefundRules::licensePackageRefundable($subscription);
  //   $invoices = array_map(fn($invoice) => $invoice->invoice, $result->getInvoices());

  //   $response = [
  //     'result'            => $result->isRefundable() ? 'refundable' : 'not_refundable',
  //     'reason'            => $result->getReason(),
  //     'refundable_amount' => $result->getRefundableAmount(),
  //     'subscription'      => $this->transformSingleResource($subscription),
  //     'invoices'          => $this->transformMultipleResources($invoices),
  //   ];
  //   return response()->json($response);
  // }

  // GET account/subscriptions/{id}/paddle-link
  public function accountGetPaddleLink(int $id)
  {
    $this->validateUser();

    /** @var Subscription $subscription */
    $subscription = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->paddleManager->subscriptionService->getManagementLinks($subscription);
  }
}
