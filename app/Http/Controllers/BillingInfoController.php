<?php

namespace App\Http\Controllers;

use App\Models\BillingInfo;
use App\Models\User;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\Locale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingInfoController extends SimpleController
{
  protected string $modelClass = BillingInfo::class;


  public function __construct(public SubscriptionManager $manager)
  {
    parent::__construct();
  }

  protected function getUpdateRules(array $inputs = []): array
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
      "tax_id"            => ['nullable', 'array'],
      "tax_id.type"       => ['required_with:tax_id', 'string', 'max:255'],
      "tax_id.value"      => ['required_with:tax_id', 'string', 'max:255'],
      "language"          => ['filled', Rule::in(Locale::languages($inputs['address']['country'] ?? ''))],
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
    $inputs = $this->validateUpdate($request, $billingInfo->id);

    // if there is active pay subscription, it is not allowed to update country/state/postcode
    if ($this->user->getActivePaidSubscription()) {
      if (
        isset($inputs['country']) && $inputs['country'] != $billingInfo->address['country'] ||
        isset($inputs['postcode']) && $inputs['postcode'] != $billingInfo->address['postcode'] ||
        isset($inputs['state']) && $inputs['state'] != $billingInfo->address['state']
      ) {
        return response()->json(
          ['message' => 'BillingInfos state/postcode/country can not be modified when there is active paid subscription.'],
          400
        );
      }
    }

    if (isset($inputs['address']['country']) && !isset($inputs['language'])) {
      $inputs['language'] = Locale::defaultLanguage($inputs['address']['country']);
    }

    $billingInfo->forceFill($inputs);
    if (
      !$billingInfo->address['line1'] ||
      !$billingInfo->address['city'] ||
      !$billingInfo->address['state'] ||
      !$billingInfo->address['postcode'] ||
      !$billingInfo->address['country']
    ) {
      return response()->json(
        ['message' => 'BillingInfo address(line1/city/state/postcode/country) is not valid.'],
        400
      );
    }
    $billingInfo->save();

    // create or update customer
    // TODO: when DR go online, DR: customer may need to update
    $this->manager->createOrUpdateCustomer($billingInfo);

    return $this->transformSingleResource($billingInfo->unsetRelations());
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
