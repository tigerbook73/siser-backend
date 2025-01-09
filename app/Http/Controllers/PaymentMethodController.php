<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\User;

class PaymentMethodController extends SimpleController
{
  protected string $modelClass = PaymentMethod::class;

  public function __construct()
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
