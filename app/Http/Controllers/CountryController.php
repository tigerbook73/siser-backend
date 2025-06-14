<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CountryController extends SimpleController
{
  protected string $modelClass = Country::class;


  protected function getListRules(array $inputs = []): array
  {
    return [
      'code'      => ['filled'],
      'name'      => ['filled'],
      'currency'  => ['filled'],
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'code'                    => ['required', 'string', 'size:2', 'unique:countries'],
      'name'                    => ['required', 'string', 'max:255'],
      'currency'                => ['required', 'string', 'size:3'],
    ];
  }

  protected function getUpdateRules(array $inputs = []): array
  {
    return [
      'name'                    => ['filled', 'string', 'max:255'],
      'currency'                => ['filled', 'string', 'size:3'],
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
    $country->forceFill($inputs);
    DB::transaction(
      fn () => $country->save()
    );
    return $this->transformSingleResource($country->unsetRelations());
  }

  public function destroyWithCode(string $code)
  {
    $this->validateUser();

    /** @var Country $country */
    $country = Country::code($code)->firstOrFail();
    if (Plan::whereJsonContains('price_list', ['country' => $country->code])->count() > 0) {
      return response()->json(['message' => 'The country has been referenced'], 422);
    };

    return DB::transaction(
      fn () => $country->delete()
    );
  }
}
