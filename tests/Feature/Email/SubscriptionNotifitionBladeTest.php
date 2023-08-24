<?php

namespace Tests\Feature\Email;

use App\Notifications\SubscriptionNotification;
use Tests\ApiTestCase;

class SubscriptionNotifitionBladeTest extends ApiTestCase
{
  public string $baseUrl = '/be-test/notification';
  public ?string $role = 'customer';

  public $notifications = [
    SubscriptionNotification::NOTIF_ORDER_ABORTED,
    SubscriptionNotification::NOTIF_ORDER_CANCELLED,
    SubscriptionNotification::NOTIF_ORDER_CONFIRMED,
    SubscriptionNotification::NOTIF_ORDER_CREDIT_MEMO,
    SubscriptionNotification::NOTIF_ORDER_INVOICE,
    SubscriptionNotification::NOTIF_ORDER_REFUNDED,
    SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED,

    SubscriptionNotification::NOTIF_CANCELLED,
    SubscriptionNotification::NOTIF_CANCELLED_REFUND,
    SubscriptionNotification::NOTIF_EXTENDED,
    SubscriptionNotification::NOTIF_FAILED,
    SubscriptionNotification::NOTIF_INVOICE_PENDING,
    SubscriptionNotification::NOTIF_REMINDER,
    SubscriptionNotification::NOTIF_TERMINATED,
    SubscriptionNotification::NOTIF_TERMS_CHANGED,
  ];
  public $countries = ['US', 'AU', 'CA', 'DE', 'ES', 'FR', 'GB', 'IT', 'JP', 'NZ'];


  public function viewNotification(string $type, string $country)
  {
    return $this->get("{$this->baseUrl}/{$type}?country={$country}");
  }

  public function clean()
  {
    return $this->get("/be-test/clean");
  }

  public function viewNotificationType(string $type)
  {
    foreach ($this->countries as $country) {
      $this->viewNotification($type, $country)
        ->assertStatus(200)
        ->assertSeeText('Siser Software')
        ->assertDontSeeText('messages.');
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

  public function testNotificationOrderRefunded()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_REFUNDED);
  }

  public function testNotificationOrderRefundFailed()
  {
    $this->viewNotificationType(SubscriptionNotification::NOTIF_ORDER_REFUND_FAILED);
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
