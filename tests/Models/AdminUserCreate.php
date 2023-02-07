<?php
/**
 * AdminUserCreate
 */
namespace Tests\Models;

/**
 * AdminUserCreate
 */
class AdminUserCreate {

    /** @var string $name same as the username field in coginto*/
    public $name = "";

    /** @var string $email */
    public $email = "";

    /** @var string $full_name */
    public $full_name = "";

    /** @var \Tests\Models\Role[] $roles */
    public $roles = [];

    /** @var string $password */
    public $password = "";

}
