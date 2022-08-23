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
      'name'      => ['filled'],
      'platform'  => ['filled', 'in:Windows,Mac'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'name'          => ['required', 'max:255'],
      'platform'      => ['required', 'in:Windows,Mac'],
      'version'       => ['required', 'max:255'],
      'description'   => ['max:255'],
      'version_type'  => ['required', 'in:stable,beta'],
      'released_date' => ['required', 'date'],
      'release_notes' => ['max:255'],
      'filename'      => ['required', 'max:255'],
      'url'           => ['required', 'max:255'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'name'          => ['filled', 'string', 'max:255'],
      'platform'      => ['filled', 'in:Windows,Mac'],
      'version'       => ['filled', 'string', 'max:255'],
      'description'   => ['max:255'],
      'version_type'  => ['filled', 'in:stable,beta'],
      'released_date' => ['filled', 'string', 'date'],
      'release_notes' => ['max:255'],
      'filename'      => ['filled', 'string', 'max:255'],
      'url'           => ['filled', 'string', 'max:255'],
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
