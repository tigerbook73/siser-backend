<?php
/**
 * Device
 */
namespace Tests\Models;

/**
 * Device
 */
class Device {

    /** @var string $device_id unique device_id of the computer that runs LDS software. It will be a 16-digit string.*/
    public $device_id = "";

    /** @var string $device_name */
    public $device_name = "";

    /** @var string $user_code user_code for the registration. It will be a 15-digit string.*/
    public $user_code = "";

    /** @var int $expires_at */
    public $expires_at = 0;

    /** @var string $status */
    public $status = "";

    /** @var \Tests\Models\DeviceLatestAction $latest_action */
    public $latest_action;

}
