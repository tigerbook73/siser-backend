<?php

namespace App\Models;

use App\Models\Base\SoftwarePackage as BaseSoftwarePackage;

class SoftwarePackage extends BaseSoftwarePackage
{
  static protected $attributesOption = [
    'id'                  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'name'                => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'platform'            => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'version'             => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'description'         => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'version_type'        => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'released_date'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'release_notes'       => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'release_notes_text'  => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'filename'            => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'is_latest'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_1],
    'url'                 => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'file_hash'           => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'force_update'        => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'status'              => ['filterable' => 1, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_1_1, 'listable' => 0b0_1_1],
    'created_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
    'updated_at'          => ['filterable' => 0, 'searchable' => 0, 'lite' => 0, 'updatable' => 0b0_0_0, 'listable' => 0b0_1_0],
  ];

  public function beforeCreate()
  {
    if ($this->status == null) {
      $this->status = 'active';
    }
  }

  public function afterCreate()
  {
    if ($this->status == 'active') {
      SoftwarePackageLatest::updateLatest($this->name, $this->platform, $this->version_type);
    }
  }

  public function afterUpdate()
  {
    /** @var ?SoftwarePackageLatest $prevLatest */
    $prevLatest = $this->software_package_latest;
    if (
      $prevLatest &&
      ($prevLatest->name != $this->name  ||
        $prevLatest->platform != $this->platform  ||
        $prevLatest->version_type != $this->version_type)
    ) {
      SoftwarePackageLatest::updateLatest($prevLatest->name, $prevLatest->platform, $prevLatest->version_type);
    }

    SoftwarePackageLatest::updateLatest($this->name, $this->platform, $this->version_type);
  }

  public function beforeDelete()
  {
    // remove latest if required
    SoftwarePackageLatest::where('software_package_id', $this->id)->delete();
  }

  public function afterDelete()
  {
    // update latest
    SoftwarePackageLatest::updateLatest($this->name, $this->platform, $this->version_type);
  }
}
