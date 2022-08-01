<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends SimpleController
{
  protected string $modelClass = Plan::class;

  protected function getListRules()
  {
    return [
      'catagory'    => ['in:machine,software'],
    ];
  }

  public function deactivate(Request $request, $id)
  {
    $this->validateUser();

    /** @var Plan $plan */
    $plan = $this->customizeQuery($this->baseQuery(), [])->findOrFail($id);

    // validate status
    if ($plan->status !== 'active') {
      abort(400, 'Can not be deactivated');
    }


    $plan->deactivate();
    return $this->transformSingleResource($plan);
  }
}
