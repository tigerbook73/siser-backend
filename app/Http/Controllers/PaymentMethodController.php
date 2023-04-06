<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Http\Request;

class PaymentMethodController extends SimpleController
{
  protected string $modelClass = PaymentMethod::class;

  public function __construct(public SubscriptionManager $manager)
  {
    parent::__construct();
  }

  public function accountGet()
  {
    $this->validateUser();
    $paymentMethod = $this->user->payment_method()->first();
    if (!$paymentMethod) {
      return response()->json(null);
    }
    return $this->transformSingleResource($paymentMethod->unsetRelations());
  }

  public function accountSet(Request $request)
  {
    $this->validateUser();

    // validate billing info
    if (empty($this->user->billing_info->address['line1'])) {
      return response()->json(['message' => 'parameters dr.source_id is not valid'], 400);
    }

    // validate inputs
    $inputs = $request->validate([
      "type"          => ['required', 'string', 'max:255'],
      "dr"            => ['required', 'array'],
      "dr.source_id"  => ['required', 'string', 'max:255'],
    ]);

    $paymentMethod = $this->manager->updatePaymentMethod($this->user, $inputs['dr']['source_id']);

    return $this->transformSingleResource($paymentMethod);
  }

  public function userGet($id)
  {
    $this->validateUser();

    /** @var User $user */
    $user = User::findOrFail($id);
    $paymentMethod = $user->payment_method()->first();
    if (!$paymentMethod) {
      return response()->json(null);
    }
    return $this->transformSingleResource($paymentMethod);
  }
}
