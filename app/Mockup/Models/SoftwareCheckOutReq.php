<?php
/**
 * SoftwareCheckOutReq
 */
namespace App\Mockup\Models;

/**
 * SoftwareCheckOutReq
 */
class SoftwareCheckOutReq {

    /** @var string $version this is used for future extension. currently, it must be 1.0.*/
    public $version = "";

    /** @var string $device_id unique device_id for the host that runs the LDS software*/
    public $device_id = "";

}
