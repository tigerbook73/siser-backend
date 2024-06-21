<?php

namespace App\Http\Controllers;

use App\Models\LicenseSharing;
use Illuminate\Http\Request;

class LicenseSharingController extends SimpleController
{
  protected string $modelClass = LicenseSharing::class;

  protected function getListRules(array $inputs = []): array
  {
    return [
      'id'                => ['filled'],
      'user_id'           => ['filled'],
      'subscription_id'   => ['filled'],
      'status'            => ['filled'],
    ];
  }

  protected function getAccountListRules(array $inputs = []): array
  {
    return [];
  }

  /**
   * get /license-sharings
   *
   * default implementation
   */

  /**
   * get /license-sharings/{id}
   *
   * default implementation
   */

  /**
   * get /account/license-sharings
   */
  public function accountList(Request $request)
  {
    $this->validateUser();

    $inputs = $request->validate($this->getAccountListRules());
    $inputs['user_id'] = $this->user->id;
    $inputs['status'] = LicenseSharing::STATUS_ACTIVE;
    $objects = $this->standardQuery($inputs)->get();
    return ['data' => $this->transformMultipleResources($objects)];
  }

  /**
   * get /account/license-sharings/{id}
   */
  public function accountIndex(string $id)
  {
    $this->validateUser();
    $objects = $this->standardQuery([
      'user_id' => $this->user->id,
      'status' => LicenseSharing::STATUS_ACTIVE,
    ])->findOrFail($id);
    return response()->json($this->transformSingleResource($objects));
  }
}
