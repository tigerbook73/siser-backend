<?php
/**
 * ReportSubscriptionsRes
 */
namespace Tests\Models;

/**
 * ReportSubscriptionsRes
 */
class ReportSubscriptionsRes {

    /** @var int $new_paid_subscribers_count */
    public $new_paid_subscribers_count = 0;

    /** @var int $all_paid_subscribers_count */
    public $all_paid_subscribers_count = 0;

    /** @var int $processed_payment_lines_count */
    public $processed_payment_lines_count = 0;

    /** @var float $total_revenue_usd_converted */
    public $total_revenue_usd_converted = 0;

    /** @var \Tests\Models\ReportSubscriptionsResTotalRevenueByCurrency $total_revenue_by_currency */
    public $total_revenue_by_currency;

}
