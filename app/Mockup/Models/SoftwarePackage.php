<?php
/**
 * SoftwarePackage
 */
namespace App\Mockup\Models;

/**
 * SoftwarePackage
 */
class SoftwarePackage {

    /** @var int $id */
    public $id = 0;

    /** @var string $name */
    public $name = "";

    /** @var string $platform */
    public $platform = "";

    /** @var string $version */
    public $version = "";

    /** @var string $description */
    public $description = "";

    /** @var string $version_type */
    public $version_type = "";

    /** @var \DateTime $released_date */
    public $released_date;

    /** @var string $release_notes */
    public $release_notes = "";

    /** @var string $filename */
    public $filename = "";

    /** @var bool $is_latest */
    public $is_latest = false;

    /** @var string $url */
    public $url = "";

}
