<?php

namespace App\Http\Controllers;

use App\Models\BillingInfo;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class BillingInfoController extends SimpleController
{
  protected string $modelClass = BillingInfo::class;


  protected function getUpdateRules()
  {
    return [
      "first_name"        => ['filled', 'string', 'max:255'],
      "last_name"         => ['filled', 'string', 'max:255'],
      "phone"             => ['string', 'max:255'],
      "organization"      => ['string', 'max:255'],
      "email"             => ['filled', 'email'],
      "address"           => ['filled', 'array'],
      'address.line1'     => ['required_with:address', 'string', 'max:255'],
      'address.line2'     => ['string', 'max:255'],
      'address.city'      => ['required_with:address', 'string', 'max:255'],
      'address.postcode'  => ['required_with:address', 'string', 'max:255'],
      'address.state'     => ['required_with:address', 'string', 'max:255'],
      'address.country'   => ['required_with:address', 'string', 'exists:countries,code'],
      "tax_id"            => ['array'],
      "tax_id.type"       => ['required_with:tax_id', 'string', 'max:255'],
      "tax_id.value"      => ['required_with:tax_id', 'string', 'max:255'],
    ];
  }

  public function accountGet()
  {
    $this->validateUser();
    $billingInfo = $this->user->billing_info()->first() ?: BillingInfo::createDefault($this->user);
    return $this->transformSingleResource($billingInfo->unsetRelations());
  }

  public function accountSet(Request $request)
  {
    $this->validateUser();

    /** @var BillingInfo $billingInfo */
    $billingInfo = $this->user->billing_info()->first() ?: BillingInfo::createDefault($this->user);

    // if there is active pay subscription, it is not allowed to update country/state/postcode
    if (Subscription::where('status', 'active')->where('subscription_level', '>', 1)->count()) {
      if (
        isset($request->country) && $request->country != $billingInfo->address['country'] ||
        isset($request->postcode) && $request->postcode != $billingInfo->address['postcode'] ||
        isset($request->state) && $request->state != $billingInfo->address['state']
      ) {
        return response()->json(
          ['message' => 'BillingInfos state/postcode/country can not be modified when there is active paid subscription.'],
          400
        );
      }
    }

    // TODO: refactor
    return parent::update($request, $billingInfo->id);
  }

  public function userGet($id)
  {
    $this->validateUser();

    /** @var User $user */
    $user = User::findOrFail($id);
    $billingInfo = $user->billing_info()->first() ?: BillingInfo::createDefault($user);
    return $this->transformSingleResource($billingInfo->unsetRelations());
  }
}
