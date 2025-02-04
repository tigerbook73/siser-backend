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
   *  GET /subscriptions
   */
  // default implementation

  /**
   *  GET /subscriptions/{id}
   */
  // default implementation

  /**
   *  POST /subscriptions/{id}/cancel
   */
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


  /**
   * POST /account/subscriptions/{id}/padddle-link
   */
  public function accountGetPaddleLink(int $id)
  {
    $this->validateUser();

    /** @var Subscription $subscription */
    $subscription = $this->baseQuery()->where('user_id', $this->user->id)->findOrFail($id);
    return $this->paddleManager->subscriptionService->getManagementLinks($subscription);
  }
}
