<?php
/**
 * LdsUnregisterDevice
 */
namespace Tests\Models;

/**
 * LdsUnregisterDevice
 */
class LdsUnregisterDevice {

    /** @var int $version */
    public $version = 0;

    /** @var string $device_id unique device_id of the computer that runs LDS software. It shall be a 16-digit string.*/
    public $device_id = "";

    /** @var string $user_code user code acuqired from previous registration device operation. It shall be a 15-digit string.*/
    public $user_code = "";

}
