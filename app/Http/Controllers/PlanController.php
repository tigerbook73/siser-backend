<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlanController extends SimpleController
{
  protected string $modelClass = Plan::class;

  protected function getListRules(array $inputs = []): array
  {
    return [
      'name'                => ['filled'],
      'product_name'        => ['filled'],
      'interval'            => ['filled', 'in:month,year'],
      'subscription_level'  => ['filled', 'in:1,2'],
      'status'              => ['filled', 'in:draft,active,inactive'],
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'name'                  => ['required', 'string', 'max:255'],
      'product_name'          => ['required', 'exists:products,name'],
      'interval'              => ['required', 'in:month,year'],
      'description'           => ['string', 'max:255'],
      'subscription_level'    => ['required', 'numeric', 'between:0,9'],
      'url'                   => ['string', 'max:255'],
      'price_list'            => ['required', 'array'],
      'price_list.*.country'  => ['required', 'string', 'exists:countries,code'],
      'price_list.*.currency' => ['required', 'string', 'exists:countries,currency'],
      'price_list.*.price'    => ['required', 'decimal:0,2', 'min:0'],
    ];
  }

  protected function getUpdateRules(array $inputs = []): array
  {
    return [
      'name'                  => ['filled', 'string', 'max:255'],
      'product_name'          => ['filled', 'exists:products,name'],
      'interval'              => ['filled', 'in:month,year'],
      'description'           => ['string', 'max:255'],
      'subscription_level'    => ['filled', 'numeric', 'between:0,9'],
      'url'                   => ['string', 'max:255'],
      'price_list'            => ['filled', 'array'],
      'price_list.*.country'  => ['required', 'string', 'exists:countries,code'],
      'price_list.*.currency' => ['required', 'string', 'exists:countries,currency'],
      'price_list.*.price'    => ['required', 'decimal:0,2', 'min:0'],
    ];
  }

  protected function getUpdateRulesForDraft(array $inputs = [])
  {
    return $this->getUpdateRules($inputs);
  }

  protected function getUpdateRulesForActive(array $inputs = [])
  {
    $rules = $this->getUpdateRules($inputs);
    unset($rules['name']);
    unset($rules['product_name']);
    unset($rules['interval']);
    unset($rules['subscription_level']);
    return $rules;
  }

  public function findPriceForCountry(array $price_list, string $country): ?array
  {
    foreach ($price_list as $price) {
      if ($price['country'] === $country) {
        return $price;
      }
    }
    return null;
  }

  /**
   * GET /plans
   */
  public function listPlan(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'name'          => ['filled'],
      'product_name'  => ['filled'],
      'country'       => ['required'],
    ]);

    $country = $inputs['country'];
    unset($inputs['country']);

    /** @var Plan[] $planList */
    $planList = $this->standardQuery($inputs)
      ->public()
      ->whereJsonContains('price_list', ['country' => $country])
      ->get();

    foreach ($planList as $plan) {
      $price = $plan->getPrice($country);
      if ($price) {
        $plan->price = $price;
      }
    }

    return ['data' => $this->transformMultipleResources($planList)];
  }

  /**
   * GET /plans/{id}
   */
  public function indexPlan(Request $request, int $id)
  {
    $this->validateUser();

    $inputs = $request->validate([
      'country'     => ['required'],
    ]);

    $country = $inputs['country'];
    unset($inputs['country']);

    /** @var Plan $plan */
    $plan = $this->standardQuery($inputs)
      ->public()
      ->findOrFail($id);

    $plan->price = $this->findPriceForCountry($plan->price_list, $country);
    if (!$plan->price) {
      return response()->json(['message' => 'Not found'], 404);
    }

    return $this->transformSingleResource($plan);
  }

  /**
   * GET /design-plans
   */
  // default

  /**
   * GET /design-plans/{id}
   */
  // default

  /**
   * post /design-plans
   */
  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);

    // TODO: validate duplicated country
    // TODO: validate currency

    $plan = new Plan($inputs);
    $plan->status = 'draft';

    $plan->save();
    return  response()->json($this->transformSingleResource($plan), 201);
  }

  /**
   * patch /design-plan/{id}
   */
  public function update(Request $request, int $id)
  {
    $this->validateUser();

    /** @var Plan $plan */
    $plan = $this->baseQuery()->findOrFail($id);

    $inputs = $request->all();
    if ($plan->status === 'draft') {
      $rules = $this->getUpdateRulesForDraft($inputs);
    } else if ($plan->status === 'active') {
      $rules = $this->getUpdateRulesForActive($inputs);
    } else {
      return response()->json(['message' => "Design plan in {$plan->status} status can not be updated"], 400);
    }
    $inputs = $this->validateRules($inputs, $rules);
    if (empty($inputs)) {
      abort(400, 'input data can not be empty.');
    }

    // validate and update attributers
    $updatable = $this->modelClass::getUpdatable($this->userType);
    foreach ($inputs as $attr => $value) {
      if (!in_array($attr, $updatable)) {
        abort(400, 'attribute: [' . $attr . '] is not updatable.');
      }
      $plan->$attr = $value;
    }

    DB::transaction(
      fn () => $plan->save()
      // TODO: update all active subscriptions
    );
    return $this->transformSingleResource($plan->unsetRelations());
  }

  /**
   * delete /design-plans/{id}
   */
  public function destroy(int $id)
  {
    $this->validateUser();

    if ($id == config('siser.plan.default_machine_plan')) {
      return response()->json(['message' => 'Default basic plan can not be deleted'], 400);
    }

    /** @var Plan $plan */
    $plan = $this->baseQuery()->findOrFail($id);
    if ($plan->status !== "draft") {
      return response()->json(['message' => 'Only draft plan can be deleted'], 400);
    }

    return DB::transaction(
      fn () => $plan->delete()
    );
  }

  /**
   * post /design-plans/{id}/actiate
   */
  public function activate(Request $request, int $id)
  {
    $this->validateUser();

    /** @var Plan $plan */
    $plan = $this->baseQuery()->findOrFail($id);
    if ($plan->status !== "draft") {
      return response()->json(['message' => 'Only draft plan can be activated'], 400);
    }

    $plan->status = 'active';
    $plan->save();

    return $this->transformSingleResource($plan);
  }

  /**
   * post /design-plans/{id}/deactiate
   */
  public function deactivate(Request $request, int $id)
  {
    $this->validateUser();

    if ($id == config('siser.plan.default_machine_plan')) {
      return response()->json(['message' => 'Default basic plan can not be deactivated'], 400);
    }

    /** @var Plan $plan */
    $plan = $this->baseQuery()->findOrFail($id);
    if ($plan->status !== 'active') {
      return response()->json(['message' => 'Only active plan can be dactivated'], 400);
    }

    $plan->status = 'inactive';
    $plan->save();

    return $this->transformSingleResource($plan);
  }
}
