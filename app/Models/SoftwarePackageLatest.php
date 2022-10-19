<?php

namespace App\Models;

use App\Models\Base\SoftwarePackageLatest as BaseSoftwarePackageLatest;

function versionCompare(string $v1, string $v2): int
{
  $maxSegment = 3;
  $version1 = explode('.', $v1);
  $version2 = explode('.', $v2);

  // if count is different, the longest is larger
  if (count($version1) < $maxSegment || count($version2) < $maxSegment) {
    return count($version1) - count($version2);
  }

  // compare version
  for ($index = 0; $index < $maxSegment; $index++) {
    if ($version1[$index] < $version2[$index]) {
      return -1;
    }
    if ($version1[$index] > $version2[$index]) {
      return 1;
    }
  }
  return 0;
}

class SoftwarePackageLatest extends BaseSoftwarePackageLatest
{
  static public function updateLatest(string $name, string $platform, string $version_type)
  {
    /** @var SoftwarePackageLatest|null $latest */
    $latest = SoftwarePackageLatest::where([
      'name' => $name,
      'platform' => $platform,
      'version_type' => $version_type,
    ])->first();

    /** @var SoftwarePackage[] $softwarePackages */
    $softwarePackages = SoftwarePackage::where([
      'name' => $name,
      'platform' => $platform,
      'version_type' => $version_type,
      'status' => 'active',
    ])->get();

    $latestPackage = null;
    foreach ($softwarePackages as $softwarePackage) {
      if (!$latestPackage || versionCompare($latestPackage->version, $softwarePackage->version) < 0) {
        $latestPackage = $softwarePackage;
      }
    }

    // if latest changes
    if ($latest && $latestPackage && $latest->software_package_id != $latestPackage->id) {
      $latest->software_package_id = $latestPackage->id;
      $latest->save();
      return $latest;
    }

    // if new latest
    if (!$latest && $latestPackage) {
      $latest = new SoftwarePackageLatest([
        'name'                => $name,
        'platform'            => $platform,
        'version_type'        => $version_type,
        'software_package_id' => $latestPackage->id,
      ]);
      $latest->save();
      return $latest;
    }

    // if remove latest
    if ($latest && !$latestPackage) {
      $latest->delete();
      return null;
    }
  }
}
