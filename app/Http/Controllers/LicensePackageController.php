<?php

namespace App\Http\Controllers;

use App\Models\LicensePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LicensePackageController extends SimpleController
{
  protected string $modelClass = LicensePackage::class;

  protected function getListRules(array $inputs = []): array
  {
    return [
      'type'            => ['filled'],
      'name'            => ['filled'],
      'status'          => ['filled'],
    ];
  }

  protected function getCreateRules(array $inputs = []): array
  {
    return [
      'type'          => ['required', 'string', Rule::in([LicensePackage::TYPE_STANDARD, LicensePackage::TYPE_EDUCATION])],
      'name'          => ['required', 'string', 'max:255'],
      'price_table'   => ['required', 'array'],
      'status'        => ['required', Rule::in([LicensePackage::STATUS_ACTIVE, LicensePackage::STATUS_INACTIVE])],
    ];
  }

  protected function getUpdateRules(array $inputs = []): array
  {
    return [
      'name'          => ['required', 'string', 'max:255'],
      'price_table'   => ['required', 'array'],
      'status'        => ['required', Rule::in([LicensePackage::STATUS_ACTIVE, LicensePackage::STATUS_INACTIVE])],
    ];
  }

  protected function validateMore(array $inputs): array
  {
    // validate price_table
    $priceTable = LicensePackage::validatePriceTable($inputs['price_table']);
    if (!$priceTable) {
      abort(response()->json(['message' => 'price_table is not valid.'], 400));
    };

    $inputs['price_table'] = $priceTable;
    return $inputs;
  }

  protected function validateAndSave(LicensePackage $licensePackage)
  {
    DB::transaction(function () use ($licensePackage) {
      $licensePackage->save();

      // only one active license package is allowed for each type
      if (
        LicensePackage::where('status', LicensePackage::STATUS_ACTIVE)
        ->where('type', $licensePackage->type)
        ->count() > 1
      ) {
        abort(response()->json(['message' => 'Only one active license package is allowed for each type.'], 400));
      }
    });
  }

  /**
   * get /license-packages
   */
  // default implementation

  /**
   * get /customer/license-packages
   */
  public function accountList(Request $request)
  {
    $request->merge(['status' => LicensePackage::STATUS_ACTIVE]);
    return $this->list($request);
  }

  /**
   * post /license-packages
   */
  public function create(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateCreate($request);
    $inputs = $this->validateMore($inputs);
    $licensePackage = new LicensePackage($inputs);
    $this->validateAndSave($licensePackage);

    return  response()->json($this->transformSingleResource($licensePackage), 201);
  }

  /**
   * patch /license-package/{id}
   */
  public function update(Request $request, int $id)
  {
    $this->validateUser();

    /** @var LicensePackage $licensePackage */
    $licensePackage = $this->baseQuery()->findOrFail($id);
    $inputs = $this->validateUpdate($request, $id);
    $inputs = $this->validateMore($inputs);
    $licensePackage->forceFill($inputs);
    $this->validateAndSave($licensePackage);

    return $this->transformSingleResource($licensePackage->unsetRelations());
  }

  /**
   * delete /license-packages/{id}
   */
  public function destroy(int $id)
  {
    $this->validateUser();

    /** @var LicensePackage $licensePackage */
    $licensePackage = $this->baseQuery()->findOrFail($id);
    // TODO: subscription
    // if ($licensePackage->subscriptions()->count() > 0) {
    //   return response()->json(['message' => 'LicensePackage has been used, can not be deleted'], 400);
    // }
    $licensePackage->delete();
  }
}
