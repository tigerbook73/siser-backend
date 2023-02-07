<?php
/**
 * AdminUser
 */
namespace Tests\Models;

/**
 * AdminUser
 */
class AdminUser {

    /** @var int $id */
    public $id = 0;

    /** @var string $name same as the username field in coginto*/
    public $name = "";

    /** @var string $email */
    public $email = "";

    /** @var string $full_name */
    public $full_name = "";

    /** @var \Tests\Models\Role[] $roles */
    public $roles = [];

}
