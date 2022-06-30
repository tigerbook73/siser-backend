<?php
/**
 * AdminUser
 */
namespace App\Mockup\Models;

/**
 * AdminUser
 */
class AdminUser {

    /** @var int $id */
    public $id = 0;

    /** @var string $name */
    public $name = "";

    /** @var string $full_name */
    public $full_name = "";

    /** @var string $email */
    public $email = "";

    /** @var \App\Mockup\Models\Role[] $roles */
    public $roles = [];

}
