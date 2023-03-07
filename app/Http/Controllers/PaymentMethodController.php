<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentMethodController extends SimpleController
{
  protected string $modelClass = PaymentMethod::class;

  protected function getCreateRules()
  {
    return [
      "type"          => ['required', 'string', 'in:credit-card'],
      "dr"            => ['required', 'array'],
      "dr.source_id"  => ['required', 'string', 'max:255'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      "type"          => ['filled', 'string', 'max:255'],
      'dr'            => ['filled'],
      'dr.source_id'  => ['required_with:dr', 'string', 'max:255'],
    ];
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

    /** @var PaymentMethod|null $paymentMethod */
    $paymentMethod = $this->user->payment_method()->first();

    if (!$paymentMethod) {
      $inputs = $this->validateCreate($request);

      $paymentMethod = new PaymentMethod($inputs);

      // TODO: the following is mockup code
      if (str_contains($inputs['dr']['source_id'], 'master')) {
        $paymentMethod->display_data = [
          'last_four_digits'  => '9999',
          'brand'             => 'master',
        ];
      } else {
        $paymentMethod->display_data = [
          'last_four_digits'  => '8888',
          'brand'             => 'visa',
        ];
      }
      $paymentMethod->id      = $this->user->id;
      $paymentMethod->user_id = $this->user->id;
      $paymentMethod->save();

      return  response()->json($this->transformSingleResource($paymentMethod), 201);
    } else {
      $inputs = $this->validateUpdate($request, $paymentMethod->id);
      if (empty($inputs)) {
        abort(400, 'input data can not be empty.');
      }

      $paymentMethod->type        = $inputs['type'] ?? $paymentMethod->type;
      $paymentMethod->dr          = $inputs['dr'] ?? $paymentMethod->dr;
      // TODO: the following is mockup code
      if (str_contains($inputs['dr']['source_id'], 'master')) {
        $paymentMethod->display_data = [
          'last_four_digits'  => '9999',
          'brand'             => 'master',
        ];
      } else {
        $paymentMethod->display_data = [
          'last_four_digits'  => '8888',
          'brand'             => 'visa',
        ];
      }

      // TODO: create DR customer is not exist, consistency protection
      $paymentMethod->save();

      // TODO: update DR customer
      // POST ...

      return $this->transformSingleResource($paymentMethod->unsetRelations());
    }
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
    return $this->transformSingleResource($paymentMethod->unsetRelations());
  }
}
