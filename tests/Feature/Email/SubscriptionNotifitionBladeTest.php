<?php

namespace Tests\Feature\Email;

use App\Notifications\SubscriptionNotification;
use Tests\ApiTestCase;

class SubscriptionNotifitionBladeTest extends ApiTestCase
{
  public string $baseUrl = '/be-test/notification/subscription';
  public ?string $role = 'customer';

  public $countries = ['US' /* , 'AU', 'DE' */];
  public $plans = ['month', 'year'];
  public $coupons = ['', 'free-trial', 'percentage', 'percentage-fixed-term'];

  public function viewNotification(string $type, string $country, string $plan, string $coupon = "")
  {
    return $this->get("{$this->baseUrl}/{$type}/view?country={$country}&plan={$plan}&coupon={$coupon}");
  }

  public function clean()
  {
    return $this->get("/be-test/notification/subscription-clean");
  }

  public function viewNotificationType(string $type)
  {
    foreach ($this->countries as $country) {
      foreach ($this->plans as $plan) {
        foreach ($this->coupons as $coupon) {
          $response = $this->viewNotification($type, $country, $plan, $coupon);
          $message = __FUNCTION__ . " - $country - $type - $plan - $coupon fails at: ";
          $this->assertTrue($response->getStatusCode() === 200, $message . ' status check!');
          $this->assertTrue(str_contains($response->getContent(), 'Team Siser'), $message . ' contain check!');
          $this->assertFalse(str_contains($response->getContent(), 'messages.'), $message . ' not contain check!');
        }
      }
    }
  }

  public function testNormalNotifications()
  {
    $types = array_keys(SubscriptionNotification::$types);
    foreach ($types as $type) {
      $this->viewNotificationType($type);
    }
  }
}
