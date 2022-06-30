<?php

namespace App\Http\Controllers;

use App\Models\SoftwarePackage;
use Illuminate\Http\Request;

class SoftwarePackageController extends SimpleController
{
  protected string $modelClass = SoftwarePackage::class;
  protected string $orderDirection = 'desc';   // default is 'desc'

  public function list(Request $request)
  {
    $result = parent::list($request);

    // get latest
    $windowsLatest = SoftwarePackage::getLatest('Windows');
    $macLatest = SoftwarePackage::getLatest('Mac');

    for ($i = 0; $i < count($result['data']); $i++) {
      if (
        $result['data'][$i]['id'] === $windowsLatest?->id ||
        $result['data'][$i]['id'] === $macLatest?->id
      ) {
        $result['data'][$i]['is_latest'] = true;
      }
    }
    return $result;
  }

  public function index($id)
  {
    $result = parent::index($id);

    // get latest
    $windowsLatest = SoftwarePackage::getLatest('Windows');
    $macLatest = SoftwarePackage::getLatest('Mac');

    if (
      $result['id'] === $windowsLatest?->id ||
      $result['id'] === $macLatest?->id
    ) {
      $result['is_latest'] = true;
    }

    return $result;
  }
}
