<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Http\Request;

class SubscriptionController extends SimpleController
{
  protected string $modelClass = Subscription::class;

  public function __construct(public SubscriptionManagerPaddle $manager)
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

  /**
   * GET /account/subscriptions
   */
  public function accountList(Request $request)
  {
    $request->merge(['user_id' => auth('api')->id()]);
    return parent::list($request);
  }

  /**
   * GET account/subscriptions/{id}
   */
  public function accountIndex(int $id)
  {
    $this->validateUser();
    $object = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->transformSingleResource($object);
  }

  /**
   * GET /users/{id}/subscriptions
   */
  public function listByUser(Request $request, $user_id)
  {
    $request->merge(['user_id' => $user_id]);
    return parent::list($request);
  }

  /**
   *  POST /account/subscriptions/{id}/cancel
   */
  public function accountCancel(Request $request, int $id)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'immediate'     => ['required', 'bool'],
    ]);

    return $this->cancelSubscription($id, (bool)$inputs['immediate'], $this->user->id);
  }

  /**
   *  POST /account/subscriptions/{id}/dont-cancel
   */
  public function accountDontCancel(int $id)
  {
    $this->validateUser();

    return $this->dontCancelSubscription($id, $this->user->id);
  }

  /**
   * POST /account/subscriptions/{id}/padddle-link
   */
  public function accountGetPaddleLink(int $id)
  {
    $this->validateUser();

    /** @var Subscription $subscription */
    $subscription = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->manager->subscriptionService->getManagementLinks($subscription);
  }

  /**
   *  GET /subscriptions
   */
  // default implementation

  /**
   *  GET /subscriptions/{id}
   */
  // default implementation

  /**
   * POST /subscriptions/{id}/cancel
   */
  public function cancel(Request $request, int $id)
  {
    $inputs = $request->validate([
      'immediate'     => ['required', 'bool'],
    ]);

    return $this->cancelSubscription($id, $inputs['immediate']);
  }

  /**
   * POST /subscriptions/{id}/dont-cancel
   */
  public function dontCancel(int $id)
  {
    return $this->dontCancelSubscription($id);
  }

  /**
   * common method for cancel subscription
   */
  public function cancelSubscription(int $subscriptionId, bool $immediate, ?int $userId = null)
  {
    $this->validateUser();

    $subscription = Subscription::findById($subscriptionId);

    // if userId shall be validated
    if ($userId && $subscription->user_id != $userId) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    if (
      $subscription->status != Subscription::STATUS_ACTIVE ||
      $subscription->subscription_level < 2
    ) {
      return response()->json(['message' => 'Subscription is not active or not paid'], 422);
    }

    if ($subscription->sub_status === Subscription::SUB_STATUS_CANCELLING) {
      return response()->json(['message' => 'Subscription is already on cancelling'], 422);
    }

    try {
      $subscription = $this->manager->subscriptionService->cancelSubscription($subscription, $immediate);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }

  /**
   * common method for dont cancel subscription
   */
  public function dontCancelSubscription(int $subscriptionId, ?int $userId = null)
  {
    $this->validateUser();

    $subscription = Subscription::findById($subscriptionId);

    // if userId shall be validated
    if ($userId && $subscription->user_id != $userId) {
      return response()->json(['message' => 'Subscription not found'], 404);
    }

    if ($subscription->sub_status != Subscription::SUB_STATUS_CANCELLING) {
      return response()->json(['message' => 'Subscription is not on cancelling'], 422);
    }

    try {
      $subscription = $this->manager->subscriptionService->dontCancelSubscription($subscription);
      return  response()->json($this->transformSingleResource($subscription));
    } catch (\Throwable $th) {
      return response()->json(['message' => $th->getMessage()], $this->toHttpCode($th->getCode()));
    }
  }
}
