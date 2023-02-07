<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends SimpleController
{
  protected string $modelClass = Country::class;


  protected function getListRules()
  {
    return [
      'code'      => ['filled'],
      'name'      => ['filled'],
      'currency'  => ['filled'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'code'                    => ['required', 'string', 'size:2', 'unique:countries'],
      'name'                    => ['required', 'string', 'max:255'],
      'currency'                => ['required', 'string', 'size:3'],
      'processing_fee_rate'     => ['required', 'numeric', 'between:0,50'],
      'explicit_processing_fee' => ['required', 'boolean'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'name'                    => ['filled', 'string', 'max:255'],
      'currency'                => ['filled', 'string', 'size:3'],
      'processing_fee_rate'     => ['filled', 'numeric', 'between:0,50'],
      'explicit_processing_fee' => ['filled', 'boolean'],
    ];
  }

  public function indexWithCode(string $code)
  {
    $this->validateUser();

    $country = $this->baseQuery()
      ->code($code)
      ->firstOrFail();
    return $this->transformSingleResource($country);
  }

  public function updateWithCode(Request $request, string $code)
  {
    $this->validateUser();

    /** @var Country $country */
    $country = Country::code($code)->firstOrFail();
    $inputs = $this->validateUpdate($request, $country->id);
    if (empty($inputs)) {
      abort(400, 'input data can not be empty.');
    }

    // validate and update attributers
    $updatable = $this->modelClass::getUpdatable($this->userType);
    foreach ($inputs as $attr => $value) {
      if (!in_array($attr, $updatable)) {
        abort(400, 'attribute: [' . $attr . '] is not updatable.');
      }
      $country->$attr = $value;
    }

    DB::transaction(
      fn () => $country->save()
    );
    return $this->transformSingleResource($country->unsetRelations());
  }

  public function destroyWithCode(string $code)
  {
    $this->validateUser();

    $country = Country::code($code)->firstOrFail();
    return DB::transaction(
      fn () => $country->delete()
    );
  }
}
