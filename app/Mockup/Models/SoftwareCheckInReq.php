<?php
/**
 * SoftwareCheckInReq
 */
namespace App\Mockup\Models;

/**
 * SoftwareCheckInReq
 */
class SoftwareCheckInReq {

    /** @var string $version this is used for future extension. currently, it must be 1.0.*/
    public $version = "";

    /** @var string $device_id unique device_id for the host that runs the LDS software*/
    public $device_id = "";

    /** @var int $timeout requested license timeout seconds*/
    public $timeout = 3600;

}
