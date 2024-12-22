<?php
/**
 * SubscriptionDR
 */
namespace Tests\Models;

/**
 * SubscriptionDR
 */
class SubscriptionDR {

    /** @var string $checkout_id the dr&#39;s checkout_id to create the subscription*/
    public $checkout_id = "";

    /** @var string $checkout_payment_session_id the dr&#39;s checkout payment session id for make payment*/
    public $checkout_payment_session_id = "";

    /** @var string $order_id the dr&#39;s first order_id to create the subscription*/
    public $order_id = "";

    /** @var string $subscription_id the dr&#39;s subscription_id*/
    public $subscription_id = "";

    /** @var string $email welcome back email has been sent for this customer*/
    public $email = 'welcome-back';

    /** @var string $stopped the digital river subscription has been stopped*/
    public $stopped = 'by-force';

    /** @var \DateTime $first_attempt_at the first accept date to retain customer*/
    public $first_attempt_at;

    /** @var \DateTime $last_attempt_at the last accept date to retain customer*/
    public $last_attempt_at;

}
