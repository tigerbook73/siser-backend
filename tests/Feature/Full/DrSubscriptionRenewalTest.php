<?php

namespace Tests\Feature\Full;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionRenewal;
use App\Notifications\SubscriptionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\DR\DrApiTestCase;

class DrSubscriptionRenewalTest extends DrApiTestCase
{
  public ?string $role = 'customer';

  /**
   * default init: no renewal required
   */
  public function standardInit()
  {
    $this->createOrUpdateBillingInfo();
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription(Plan::INTERVAL_YEAR);
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccepted(Subscription::find($response->json('id')));

    return $subscription;
  }

  /**
   * init for renewal test
   */
  public function init()
  {
    $this->createOrUpdateBillingInfo(
      [
        'first_name'    => 'first_name',
        'last_name'     => 'last_name',
        'phone'         => '',
        'organization'  => '',
        'email'         => 'test-case@me.com',
        'address' => [
          'line1'       => '123 ABC Street',
          'line2'       => '',
          'city'        => 'TEST City',
          'postcode'    => '1000',
          'state'       => 'TEST',
          'country'     => 'DE',
        ]
      ]

    );
    $this->createOrUpdatePaymentMethod();
    $response = $this->createSubscription(Plan::INTERVAL_YEAR);
    $response = $this->paySubscription($response->json('id'));
    $subscription = $this->onOrderAccepted(Subscription::find($response->json('id')));

    $this->assertEquals(SubscriptionPlan::INTERVAL_YEAR, $subscription->plan_info['interval']);

    $this->assertNotNull($subscription->renewal_info);
    $this->assertEquals(SubscriptionRenewal::STATUS_PENDING, $subscription->renewal_info['status']);
    $this->assertEquals(
      $subscription->next_invoice_date->subDays(config('dr.renewal.start_offset'))->toISOString(),
      ($subscription->renewal_info['start_at'])
    );
    $this->assertEquals(
      $subscription->next_invoice_date->subDays(config('dr.renewal.expire_offset'))->toISOString(),
      ($subscription->renewal_info['expire_at'])
    );
    $this->assertEquals(
      $subscription->next_invoice_date->subDays(config('dr.renewal.first_reminder_offset'))->toISOString(),
      ($subscription->renewal_info['first_reminder_at'])
    );
    $this->assertEquals(
      $subscription->next_invoice_date->subDays(config('dr.renewal.final_reminder_offset'))->toISOString(),
      ($subscription->renewal_info['final_reminder_at'])
    );

    return $subscription;
  }

  public function try_first_reminder(Subscription $subscription, $offsetDays = 0, $trigger = 'artisan')
  {
    if (!$subscription->renewal_info) {
      return $subscription;
    }

    $fakeNow = $subscription->next_invoice_date->subDays(config('dr.renewal.start_offset') - $offsetDays);

    $currentStatus = $subscription->renewal_info['status'];
    $currentSubStatus = $subscription->renewal_info['sub_status'];

    // first reminder
    Notification::fake();

    Carbon::setTestNow($fakeNow);
    if ($trigger === 'artisan') {
      $this->artisan('subscription:renew-annual');
    } else {
      $this->onSubscriptionReminder($subscription);
    }
    $subscription->refresh();

    if (
      $currentStatus === SubscriptionRenewal::STATUS_PENDING ||
      ($currentStatus === SubscriptionRenewal::STATUS_ACTIVE  &&  $currentSubStatus ===  SubscriptionRenewal::SUB_STATUS_READY)
    ) {
      // ready for first reminder
      $this->assertEquals(SubscriptionRenewal::STATUS_ACTIVE, $subscription->renewal_info['status']);
      $this->assertEquals(SubscriptionRenewal::SUB_STATUS_FIRST_REMINDERED, $subscription->renewal_info['sub_status']);
      Notification::assertSentTo(
        $subscription,
        fn(SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_RENEW_REQUIRED
      );
    } else {
      // first reminder already sent, no reminder and renewal needed
      $this->assertEquals($currentStatus, $subscription->renewal_info['status']);
      $this->assertEquals($currentSubStatus, $subscription->renewal_info['sub_status']);
      Notification::assertNothingSent();
    }

    return $subscription;
  }

  public function try_final_reminder(Subscription $subscription, $offsetDays = 0)
  {
    if (!$subscription->renewal_info) {
      return $subscription;
    }

    $fakeNow = $subscription->next_invoice_date->subDays(config('dr.renewal.final_reminder_offset') - $offsetDays);

    $currentStatus = $subscription->renewal_info['status'];
    $currentSubStatus = $subscription->renewal_info['sub_status'];

    // final reminder
    Notification::fake();

    Carbon::setTestNow($fakeNow);
    $this->artisan('subscription:renew-annual');
    $subscription->refresh();

    if (
      $currentStatus === SubscriptionRenewal::STATUS_PENDING ||
      ($currentStatus === SubscriptionRenewal::STATUS_ACTIVE && $currentSubStatus !==  SubscriptionRenewal::SUB_STATUS_FINAL_REMINDERED)
    ) {
      // ready for final reminder
      $this->assertEquals(SubscriptionRenewal::STATUS_ACTIVE, $subscription->renewal_info['status']);
      $this->assertEquals(SubscriptionRenewal::SUB_STATUS_FINAL_REMINDERED, $subscription->renewal_info['sub_status']);
      Notification::assertSentTo(
        $subscription,
        fn(SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_RENEW_REQUIRED
      );
    } else {
      // final reminder already sent, no reminder and renewal needed
      $this->assertEquals($currentStatus, $subscription->renewal_info['status']);
      $this->assertEquals($currentSubStatus, $subscription->renewal_info['sub_status']);
      Notification::assertNothingSent();
    }

    return $subscription;
  }

  public function try_complete(Subscription $subscription)
  {
    if (!$subscription->renewal_info && $subscription->renewal_info['status'] !== Subscription::STATUS_ACTIVE) {
      return null;
    }

    Notification::fake();

    $response = $this->postJson("/api/v1/account/subscriptions/{$subscription->id}/renewal");

    $subscription->refresh();
    $this->assertEquals(SubscriptionRenewal::STATUS_COMPLETED, $subscription->renewal_info['status']);

    if ($subscription->getActiveInvoice()) {
      Notification::assertSentTo(
        $subscription,
        fn(SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_RENEW_REQ_CONFIRMED
      );
      Notification::assertSentTo(
        $subscription,
        fn(SubscriptionNotification $notification) => $notification->type == SubscriptionNotification::NOTIF_REMINDER
      );
    }

    return $response;
  }

  public function try_cancel(Subscription $subscription)
  {
    $this->cancelSubscription($subscription);

    $this->assertNull($subscription->renewal_info);

    return $subscription;
  }

  public function try_expire(Subscription $subscription, $offsetDays = 0)
  {
    if (!$subscription->renewal_info || $subscription->renewal_info['status'] !== SubscriptionRenewal::STATUS_ACTIVE) {
      return $subscription;
    }

    $fakeNow = $subscription->next_invoice_date->subDays(config('dr.renewal.expire_offset') - $offsetDays);

    $currentStatus = $subscription->renewal_info['status'];
    $currentSubStatus = $subscription->renewal_info['sub_status'];

    // final reminder
    Notification::fake();

    if ($currentStatus === SubscriptionRenewal::STATUS_ACTIVE) {
      $this->mockCancelSubscription();
    }

    Carbon::setTestNow($fakeNow);
    $this->artisan('subscription:renew-annual');
    $subscription->refresh();

    if ($currentStatus === SubscriptionRenewal::STATUS_ACTIVE) {
      // expire
      $this->assertEquals(SubscriptionRenewal::STATUS_EXPIRED, $subscription->renewal_info['status']);
      $this->assertNull($subscription->next_invoice_date);
    } else {
      // completed or cancelled, remain old status
      $this->assertEquals($currentStatus, $subscription->renewal_info['status']);
      $this->assertEquals($currentSubStatus, $subscription->renewal_info['sub_status']);
      Notification::assertNothingSent();
    }

    return $subscription;
  }

  public function test_first_reminder_1()
  {
    $subscription = $this->init();

    // first reminder
    $this->try_first_reminder($subscription, 0);
    $this->try_first_reminder($subscription, 1);
    $this->try_first_reminder($subscription, 1, 'event');
    $this->try_first_reminder($subscription, config('dr.renewal.start_offset') - config('dr.renewal.first_reminder_offset') - 1);
  }

  public function test_first_reminder_2()
  {
    $subscription = $this->init();

    // first reminder
    $this->try_first_reminder($subscription, 0, 'event');
    $this->try_first_reminder($subscription, 1);
    $this->try_first_reminder($subscription, config('dr.renewal.start_offset') - config('dr.renewal.first_reminder_offset') - 1);
  }

  public function test_first_reminder_no_renewal()
  {
    $subscription = $this->standardInit();
    $this->try_final_reminder($subscription, 0);
  }

  public function test_first_reminder_cancelled()
  {
    $subscription = $this->init();
    $this->cancelSubscription($subscription);

    $this->try_first_reminder($subscription, 0);
  }

  public function test_first_reminder_completed()
  {
    $subscription = $this->init();
    $this->try_first_reminder($subscription, 0);
    $this->try_complete($subscription);

    $this->try_first_reminder($subscription, 0);
  }

  public function test_complete_renewal()
  {
    $subscription = $this->init();
    $this->try_first_reminder($subscription, 0, 'event');
    $this->try_complete($subscription);
  }

  public function test_final_reminder_1()
  {
    $subscription = $this->init();

    // final reminder
    $this->try_first_reminder($subscription, 0);
    $this->try_final_reminder($subscription, 1);
    $this->try_final_reminder($subscription, config('dr.renewal.final_reminder_offset') - config('dr.renewal.expire_offset') - 1);
  }

  public function test_final_reminder_2()
  {
    $subscription = $this->init();

    // final reminder
    $this->try_final_reminder($subscription, 0);
    $this->try_final_reminder($subscription, config('dr.renewal.final_reminder_offset') - config('dr.renewal.expire_offset') - 1);
  }

  public function test_final_reminder_no_renewal()
  {
    $subscription = $this->standardInit();

    $this->try_final_reminder($subscription, 0);
  }

  public function test_final_reminder_cancelled()
  {
    $subscription = $this->init();
    $this->cancelSubscription($subscription);

    $this->try_final_reminder($subscription, 0);
  }

  public function test_final_reminder_completed()
  {
    $subscription = $this->init();
    $this->try_final_reminder($subscription, 0);
    $this->try_complete($subscription);

    $this->try_final_reminder($subscription, 1);
  }

  public function test_expire_renewal_1()
  {
    $subscription = $this->init();

    $this->try_first_reminder($subscription, 0);
    $this->try_expire($subscription, 0);
    $this->try_expire($subscription, 0);
  }

  public function test_expire_renewal_2()
  {
    $subscription = $this->init();

    $this->try_final_reminder($subscription, 0);
    $this->try_expire($subscription, 0);
  }

  public function test_expire_renewal_3()
  {
    $subscription = $this->init();

    $this->try_first_reminder($subscription, 0);
    $this->try_final_reminder($subscription, 0);
    $this->try_expire($subscription, 0);
  }

  public function test_expire_completed()
  {
    $subscription = $this->init();

    $this->try_first_reminder($subscription, 0);
    $this->try_complete($subscription);
    $this->try_expire($subscription, 0);
  }

  public function test_expire_cancelled()
  {
    $subscription = $this->init();
    $this->try_cancel($subscription);
    $this->try_expire($subscription, 0);
  }
}
