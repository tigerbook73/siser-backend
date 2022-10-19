<?php

namespace App\Http\Controllers;

use App\Models\Base\SoftwarePackageLatest;
use App\Models\SoftwarePackage;
use Illuminate\Http\Request;

class SoftwarePackageController extends SimpleController
{
  protected string $modelClass = SoftwarePackage::class;
  protected string $orderDirection = 'desc';   // default is 'desc'

  protected function getListRules()
  {
    return [
      'name'          => ['filled'],
      'platform'      => ['filled', 'in:Windows,Mac'],
      'version_type'  => ['filled', 'in:stable,beta'],
      'version'       => ['filled'],
      'status'        => ['filled', 'in:active,inactive,all'],
    ];
  }

  protected function getCreateRules()
  {
    return [
      'name'                        => ['required', 'max:255'],
      'platform'                    => ['required', 'in:Windows,Mac'],
      'version'                     => ['required', 'regex:/^\d+\.\d+\.\d+/', 'max:255'],
      'description'                 => ['max:2000'],
      'version_type'                => ['required', 'in:stable,beta'],
      'released_date'               => ['required', 'date'],
      'release_notes'               => ['max:255'],
      'release_notes_text'          => ['array:lines'],
      'release_notes_text.lines.*'  => ['string'],
      'filename'                    => ['required', 'max:255'],
      'url'                         => ['required', 'max:255'],
      'file_hash'                   => ['max:255'],
      'force_update'                => ['boolean'],
      'status'                      => ['string', 'in:active,inactive'],
    ];
  }

  protected function getUpdateRules()
  {
    return [
      'name'                        => ['filled', 'string', 'max:255'],
      'platform'                    => ['filled', 'in:Windows,Mac'],
      'version'                     => ['filled', 'regex:/^\d+\.\d+\.\d+/', 'max:255'],
      'description'                 => ['max:255'],
      'version_type'                => ['filled', 'in:stable,beta'],
      'released_date'               => ['filled', 'string', 'date'],
      'release_notes'               => ['max:255'],
      'release_notes_text'          => ['array:lines'],
      'release_notes_text.lines.*'  => ['string'],
      'filename'                    => ['filled', 'string', 'max:255'],
      'url'                         => ['filled', 'string', 'max:255'],
      'file_hash'                   => ['max:255'],
      'force_update'                => ['boolean'],
      'status'                      => ['string', 'in:active,inactive'],
    ];
  }

  public function list(Request $request)
  {
    $this->validateUser();
    $inputs = $this->validateList($request);

    // adjust status
    if (!isset($inputs['status']) || $inputs['status'] === '') {
      $input['status'] = 'active';
    } else if ($inputs['status'] == 'all') {
      unset($inputs['status']);
    }

    // latests
    $latestIds = SoftwarePackageLatest::all()->map(fn ($item) => $item->software_package_id)->all();

    if (isset($inputs['version']) && $inputs['version'] == 'latest') {
      unset($inputs['version']);
      $packages = $this->standardQuery($inputs)->whereIn('id', $latestIds)->get();
    } else {
      $packages = $this->standardQuery($inputs)->get();
    }

    foreach ($packages as $package) {
      $package->is_latest = in_array($package->id, $latestIds);
    }

    return ['data' => $this->transformMultipleResources($packages)];
  }

  public function index($id)
  {
    $result = parent::index($id);
    $result['is_latest'] = SoftwarePackageLatest::where('software_package_id', $result['id'])->count() > 0;
    return $result;
  }
}
