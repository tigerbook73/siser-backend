<?php

namespace Tests\Feature;

use App\Models\SoftwarePackage;
use Tests\ApiTestCase;

class SoftwarePackageTestCase extends ApiTestCase
{
  public string $baseUrl = '/api/v1/software-packages';
  public string $model = SoftwarePackage::class;


  public SoftwarePackage $object;

  protected function setUp(): void
  {
    parent::setUp();

    $this->modelSchema = [
      "id",
      "name",
      "platform",
      "version",
      "description",
      "version_type",
      "released_date",
      "release_notes",
      "filename",
      "is_latest",
      "url",
      "file_hash",
      "force_update",
    ];

    $this->modelCreate = [
      "name"          => "Test",
      "platform"      => "Windows",
      "version"       => "99.99.99",
      "description"   => "Lenarodo Design Studio 99.99.99 for Windows",
      "version_type"  => "stable",
      "released_date" => "2022-03-21 00:00:00",
      "release_notes" => "https://st-software.siser.com/software-packages/lds/99.99.99/release_notes",
      "filename"      => "Test.99.99.99.Windows.zip",
      "url"           => "./favicon.ico",
      "file_hash"     => "xyz",
      "force_update"  => 0,
    ];

    $this->modelUpdate = [
      "name"          => "Test",
      "platform"      => "Windows",
      "version"       => "100.00.00",
      "description"   => "Lenarodo Design Studio 100.00.00 for Windows",
      "version_type"  => "stable",
      "released_date" => "2022-03-21 00:00:00",
      "release_notes" => "https://st-software.siser.com/software-packages/lds/100.00.00/release_notes",
      "filename"      => "Test.100.00.00.Windows.zip",
      "url"           => "./favicon.ico",
      "file_hash"     => "abc",
      "force_update"  => 1,
    ];

    $this->object = SoftwarePackage::first();
  }
}
