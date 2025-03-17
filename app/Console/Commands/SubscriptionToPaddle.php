<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SubscriptionToPaddle extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:to-paddle {subcmd?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'try to convert digital river subscriptions to paddle';


  /**
   * const variables
   */
  const MAX_NOTIFICATIONS = 10;  // max notifications to send per command
  const EXPIRE_DAYS = 10;
  const FIRST_ATTEMPT_DAYS = 10;  // days before the end_date
  const LAST_ATTEMPT_DAYS = 2;    // days before the end_date
  const MINI_GAP_DAYS = 1;        // days before the end_date



  public function __construct(public SubscriptionManagerPaddle $manager)
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $subcmd = $this->argument('subcmd');

    if (!$subcmd || $subcmd === 'help') {
      $this->info('Usage: php artisan subscription:to-paddle {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:              show this help message');
      $this->info('  prepare:           prepare all subscriptions (this command need only run once)');
      $this->info('  first:             send first notification to canceling subscriptions');
      $this->info('  last:              send last notification to canceling subscriptions');
      $this->info('  stop:              stop expired subscriptions');
      $this->info('  daily:             run all daily commands, including subcmd: first, last and stop');
      $this->info('');

      return Command::SUCCESS;
    }

    switch ($subcmd) {
      case 'prepare':
        $this->prepare();
        break;
      case 'first':
        $count = $this->firstAttempt(self::MAX_NOTIFICATIONS);
        $this->info("Send first notification to $count subscriptions");
        break;
      case 'last':
        $count = $this->lastAttempt(self::MAX_NOTIFICATIONS);
        $this->info("Send last notification to $count subscriptions");
        break;
      case 'stop':
        $count = $this->stopExpired(self::MAX_NOTIFICATIONS);
        $this->info("Stop $count subscriptions");
        break;
      case 'daily':
        $this->daily();
        break;
      default:
        $this->error("Unknown subcmd: $subcmd");
        break;
    }

    return self::SUCCESS;
  }

  public function prepare()
  {
    // extend end_date for all canceling subscriptions
    $count = 0;
    Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->whereNotNull('dr_subscription_id')
      ->whereNotNull('dr->subscription_id')
      ->whereColumn('end_date', '<=', 'current_period_end_date')
      ->chunkById(100, function ($subscriptions) use (&$count) {
        /** @var Collection<int, Subscription> $subscriptions */
        foreach ($subscriptions as $subscription) {
          $subscription->end_date = max($subscription->current_period_end_date, now())->addDays(self::EXPIRE_DAYS);
          $subscription->save();
          printf('.');
        }
        printf("\n");
        $count += $subscriptions->count();
      });
    $this->info("$count subscriptions' end date extended");

    // update sub_status to canceling
    $count = 0;
    Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->whereNot('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->whereNotNull('dr_subscription_id')
      ->whereNotNull('dr->subscription_id')
      ->chunkById(100, function ($subscriptions) use (&$count) {
        /** @var Collection<int, Subscription> $subscriptions */
        foreach ($subscriptions as $subscription) {
          $subscription->end_date = max($subscription->current_period_end_date, now())->addDays(self::EXPIRE_DAYS);
          $subscription->sub_status = Subscription::SUB_STATUS_CANCELLING;
          $subscription->next_invoice_date = null;
          $subscription->setNextInvoice(null);
          $subscription->save();
          printf('.');
        }
        printf("\n");
        $count += $subscriptions->count();
      });
    $this->info("$count subscriptions updated to canceling status");
  }

  public function daily()
  {
    $maxCount = self::MAX_NOTIFICATIONS;
    $maxCount -= $this->firstAttempt($maxCount);
    $maxCount -= $this->lastAttempt($maxCount);
    $maxCount -= $this->stopExpired($maxCount);

    // if there are still subscriptions to process, queue this job again
    if ($maxCount <= 0) {
      // queue this job again
      dispatch(function () {
        Artisan::queue('subscription:to-paddle');
      })->delay(now()->addSeconds(5));
      Log::info("Queue next subscription:to-paddle job in 5 seconds");
    }
    return Command::SUCCESS;
  }

  /**
   * @return int number of subscriptions processed
   */
  public function firstAttempt(int $maxCount): int
  {
    if ($maxCount <= 0) {
      return 0;
    }

    /** @var Collection<int, Subscription> $subscriptions */
    $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->whereNotNull('dr_subscription_id')
      ->whereNotNull('dr->subscription_id')
      ->where('end_date', '<', now()->addDays(self::FIRST_ATTEMPT_DAYS))
      ->where('end_date', '>', now()->addDays(self::MINI_GAP_DAYS))
      ->whereNull('dr->first_attempt_at')
      ->limit($maxCount)
      ->get();
    foreach ($subscriptions as $key => $subscription) {
      // update subscription
      $dr = $subscription->dr ?? [];
      $dr['first_attempt_at'] = now();
      $subscription->dr = $dr;
      $subscription->save();

      // send notification
      $subscription->sendNotification(SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_RENEW);
      Log::info("Send first renewal notification to subscription {$subscription->id}");
    }

    return $subscriptions->count();
  }

  /**
   * @param int $maxCount
   * @return int number of subscriptions processed
   */
  public function lastAttempt(int $maxCount)
  {
    if ($maxCount <= 0) {
      return 0;
    }

    /** @var Collection<int, Subscription> $subscriptions */
    $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->whereNotNull('dr_subscription_id')
      ->whereNotNull('dr->subscription_id')
      ->where('end_date', '<', now()->addDays(self::LAST_ATTEMPT_DAYS))
      ->where('end_date', '>', now()->addDays(self::MINI_GAP_DAYS))
      ->whereNotNull('dr->first_attempt_at')
      ->whereNull('dr->last_attempt_at')
      ->limit($maxCount)
      ->get();
    foreach ($subscriptions as $subscription) {
      // update subscription
      $dr = $subscription->dr ?? [];
      $dr['last_attempt_at'] = now();
      $subscription->dr = $dr;
      $subscription->save();

      // send notification
      $subscription->sendNotification(SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_RENEW);
      Log::info("Send second renewal notification to subscription {$subscription->id}");
    }

    return $subscriptions->count();
  }

  /**
   * @param int $maxCount
   * @return int number of subscriptions processed
   */
  public function stopExpired(int $maxCount)
  {
    if ($maxCount <= 0) {
      return 0;
    }

    /** @var Collection<int, Subscription> $subscriptions */
    $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->whereNotNull('dr_subscription_id')
      ->whereNotNull('dr->subscription_id')
      ->where('end_date', '<', now())
      ->limit($maxCount)
      ->get();
    foreach ($subscriptions as $key => $subscription) {
      // update subscription
      $subscription->stop(Subscription::STATUS_FAILED, "fails to re-subscribe to Paddle");
      $this->manager->subscriptionService->refreshLicenseSharing($subscription);

      // send notification
      $subscription->sendNotification(SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_FAILED);
      Log::info("Send terminate notification to subscription {$subscription->id}");
    }

    return $subscriptions->count();
  }
}
