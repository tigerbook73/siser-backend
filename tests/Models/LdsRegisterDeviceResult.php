<?php
/**
 * LdsRegisterDeviceResult
 */
namespace Tests\Models;

/**
 * LdsRegisterDeviceResult
 */
class LdsRegisterDeviceResult {

    /** @var int $version */
    public $version = 0;

    /** @var string $device_id unique device_id of the computer that runs LDS software. It will be a 16-digit string.*/
    public $device_id = "";

    /** @var string $device_name */
    public $device_name = "";

    /** @var \Tests\Models\User $user */
    public $user;

    /** @var string $user_code user_code for the registration. It will be a 15-digit string.*/
    public $user_code = "";

}
