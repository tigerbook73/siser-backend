<?php

namespace Tests\Feature\Email;

use App\Notifications\SubscriptionNotification;
use Tests\ApiTestCase;

class SubscriptionNotifitionBladeTest extends ApiTestCase
{
  public string $baseUrl = '/be-test/notification';
  public ?string $role = 'customer';

  public $countries = ['US', 'AU', 'CA', 'DE', 'ES', 'FR', 'GB', 'IT', 'JP', 'NZ'];
  public $plans = ['month', 'year'];
  public $coupons = ['', 'free-trial', 'percentage', 'percentage-fixed-term'];

  public function viewNotification(string $type, string $country, string $plan, string $coupon = "")
  {
    return $this->get("{$this->baseUrl}/{$type}?country={$country}&plan={$plan}&coupon={$coupon}");
  }

  public function clean()
  {
    return $this->get("/be-test/clean");
  }

  public function viewNotificationType(string $type)
  {
    foreach ($this->countries as $country) {
      foreach ($this->plans as $plan) {
        foreach ($this->coupons as $coupon) {
          $this->viewNotification($type, $country, $plan, $coupon)
            ->assertStatus(200)
            ->assertSeeText('Team Siser')
            ->assertDontSeeText('messages.');
        }
      }
    }
  }

  public function testNotificationOrderAborted()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_ABORTED);
  }

  public function testNotificationOrderCancelled()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_CANCELLED);
  }

  public function testNotificationOrderConfirmed()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_CONFIRMED);
  }

  public function testNotificationOrderCreditMemo()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO);
  }

  public function testNotificationOrderInvoice()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_INVOICE);
  }

  public function testNotificationOrderRefundFailed()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED);
  }

  public function testNotificationOrderRefunded()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_REFUNDED);
  }

  public function testNotificationCancelled()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_CANCELLED);
  }

  public function testNotificationCancelledRefund()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_CANCELLED_REFUND);
  }

  public function testNotificationExtended()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_EXTENDED);
  }

  public function testNotificationFailed()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_FAILED);
  }

  public function testNotificationInvoicePending()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_INVOICE_PENDING);
  }

  public function testNotificationReminder()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_REMINDER);
  }

  public function testNotificationTerminated()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_TERMINATED);
  }

  public function testNotificationTermsChanged()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_TERMS_CHANGED);
  }
}
