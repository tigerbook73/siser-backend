<?php

namespace App\Http\Controllers;

use App\Models\SoftwarePackage;
use Illuminate\Http\Request;

class SoftwarePackageController extends SimpleController
{
  protected string $modelClass = SoftwarePackage::class;
  protected string $orderDirection = 'desc';   // default is 'desc'

  protected function getListRules()
  {
    return [
      'name'      => [],
      'platform'  => ['in:Windows,Mac'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'name'          => ['required'],
      'platform'      => ['required', 'in:Windows,Mac'],
      'version'       => ['required'],
      'description'   => ['nullable'],
      'version_type'  => ['required'],
      'released_date' => ['required', 'date'],
      'release_notes' => ['nullable'],
      'filename'      => ['required'],
      'url'           => ['required'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'name'          => [],
      'platform'      => ['in:Windows,Mac'],
      'version'       => [],
      'description'   => [],
      'version_type'  => [],
      'released_date' => ['date'],
      'release_notes' => [],
      'filename'      => [],
      'url'           => [],
    ];
  }

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
