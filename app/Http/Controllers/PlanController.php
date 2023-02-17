<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PlanController extends SimpleController
{
  protected string $modelClass = Plan::class;

  protected function getListRules()
  {
    return [
      'name'        => ['filled'],
      'catagory'    => ['filled', 'in:machine,software'],
      'status'      => ['filled', 'in:draft,active,inactive'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'name'                  => ['required', 'string', 'max:255', 'unique:plans'],
      'catagory'              => ['required', 'in:machine,software'],
      'description'           => ['string', 'max:255'],
      'subscription_level'    => ['required', 'numeric', 'between:0,9'],
      'url'                   => ['string', 'max:255'],
      'price_list'            => ['required', 'array'],
      'price_list.*.country'  => ['required', 'string', 'exists:countries,code'],
      'price_list.*.currency' => ['required', 'string', 'exists:countries,currency'],
      'price_list.*.price'    => ['required', 'decimal:0,2', 'min:0'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'name'                  => ['filled', 'string', 'max:255', Rule::unique('plans')->ignore(request("id"))],
      'catagory'              => ['filled', 'in:machine,software'],
      'description'           => ['string', 'max:255'],
      'subscription_level'    => ['filled', 'numeric', 'between:0,9'],
      'url'                   => ['string', 'max:255'],
      'price_list'            => ['filled', 'array'],
      'price_list.*.country'  => ['required', 'string', 'exists:countries,code'],
      'price_list.*.currency' => ['required', 'string', 'exists:countries,currency'],
      'price_list.*.price'    => ['required', 'decimal:0,2', 'min:0'],
    ];
  }

  protected function getUpdateRulesForDraft()
  {
    return $this->getUpdateRules();
  }

  protected function getUpdateRulesForActive()
  {
    $rules = $this->getUpdateRules();
    unset($rules['name']);
    unset($rules['catagory']);
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
      'name'        => ['filled'],
      'catagory'    => ['filled', 'in:machine,software'],
      'country'     => ['required'],
    ]);

    $country = $inputs['country'];
    unset($inputs['country']);

    /** @var Plan[] $planList */
    $planList = $this->standardQuery($inputs)
      ->public()
      ->get();

    $returnPlanList = collect();
    foreach ($planList as $plan) {
      $plan->price = $this->findPriceForCountry($plan->price_list, $country);
      if ($plan->price) {
        $returnPlanList->push($plan);
      }
    }

    return ['data' => $this->transformMultipleResources($returnPlanList)];
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
    DB::transaction(
      fn () => $plan->save()
    );
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
      $rules = $this->getUpdateRulesForDraft();
    } else if ($plan->status === 'active') {
      $rules = $this->getUpdateRulesForActive();
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

  /**
   * TODO: mockup
   */
  public function history(Request $request, int $id)
  {
    $this->validateUser();

    /** @var Plan $plan */
    $plan = $this->baseQuery()->findOrFail($id);

    return response()->json([
      'data' => []
    ]);
  }
}
