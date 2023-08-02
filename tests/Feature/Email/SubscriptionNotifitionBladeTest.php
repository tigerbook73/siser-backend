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
    SubscriptionNotification::NOTIF_ORDER_REFUNDED,
    SubscriptionNotification::NOTIF_CONFIRMED,
    SubscriptionNotification::NOTIF_CANCELLED,
    SubscriptionNotification::NOTIF_CANCELLED_REFUND,
    SubscriptionNotification::NOTIF_REMINDER,
    SubscriptionNotification::NOTIF_INVOICE_PENDING,
    SubscriptionNotification::NOTIF_FAILED,
    SubscriptionNotification::NOTIF_EXTENDED,
    SubscriptionNotification::NOTIF_ORDER_INVOICE,
    SubscriptionNotification::NOTIF_ORDER_CREDIT,
    SubscriptionNotification::NOTIF_TERMINATED,
    SubscriptionNotification::NOTIF_TERMS_CHANGED,
  ];
  public $countries = ['US', 'AU', 'CA', 'DE', 'ES', 'FR', 'GB', 'IT', 'JP', 'NZ'];


  public function viewNotification(string $type, string $country)
  {
    return $this->get("{$this->baseUrl}/{$type}?country={$country}");
  }

  public function testSubscriptionNotification()
  {
    foreach ($this->countries as $country) {
      foreach ($this->notifications as $type) {
        $this->viewNotification($type, $country)
          ->assertStatus(200)
          ->assertSeeText('Siser Software')
          ->assertDontSeeText('messages.');
      }
    }
  }
}
