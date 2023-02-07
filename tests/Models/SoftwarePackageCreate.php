<?php
/**
 * SoftwarePackageCreate
 */
namespace Tests\Models;

/**
 * SoftwarePackageCreate
 */
class SoftwarePackageCreate {

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

    /** @var \Tests\Models\SoftwarePackageUpdateReleaseNotesText $release_notes_text */
    public $release_notes_text;

    /** @var string $filename */
    public $filename = "";

    /** @var string $url */
    public $url = "";

    /** @var string $file_hash */
    public $file_hash = "";

    /** @var bool $force_update */
    public $force_update = false;

    /** @var string $status */
    public $status = "";

}
