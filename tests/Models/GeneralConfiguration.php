<?php
/**
 * GeneralConfiguration
 */
namespace Tests\Models;

/**
 * GeneralConfiguration
 */
class GeneralConfiguration {

    /** @var int $machine_license_unit This valud defines how many license units one machine will give.*/
    public $machine_license_unit = 0;

    /** @var int $plan_reminder_offset_days The number of days before open an invoice (start to charge customer) that we need to send a remider to customer.*/
    public $plan_reminder_offset_days = 0;

    /** @var int $plan_billing_offset_days The number of days before the end of the billing period that Digital River opens an invoice.*/
    public $plan_billing_offset_days = 0;

    /** @var int $plan_collection_period_days The number of days that Digital River attempts to collect payment.*/
    public $plan_collection_period_days = 0;

    /** @var int $siser_share_rate 0-1,000,000 subscription*/
    public $siser_share_rate = 0;

}
