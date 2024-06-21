<?php
/**
 * LicenseSharingInvitation
 */
namespace Tests\Models;

/**
 * LicenseSharingInvitation
 */
class LicenseSharingInvitation {

    /** @var int $id */
    public $id = 0;

    /** @var int $license_sharing_id */
    public $license_sharing_id = 0;

    /** @var string $product_name */
    public $product_name = "";

    /** @var int $subscription_level */
    public $subscription_level = 0;

    /** @var int $owner_id */
    public $owner_id = 0;

    /** @var string $owner_name */
    public $owner_name = "";

    /** @var string $owner_email */
    public $owner_email = "";

    /** @var int $guest_id */
    public $guest_id = 0;

    /** @var string $guest_name */
    public $guest_name = "";

    /** @var string $guest_email */
    public $guest_email = "";

    /** @var \DateTime $expires_at */
    public $expires_at;

    /** @var string $status */
    public $status = "";

    /** @var \DateTime $created_at */
    public $created_at;

    /** @var \DateTime $updated_at */
    public $updated_at;

}
