<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SubscriptionStopCancelled extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:stop-cancelled {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'stop cancelled subscriptions';

  public function __construct(public SubscriptionManager $manager)
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
    Log::info('Artisan: subscription:stop-cancelled: start');

    $maxCount = 100;
    $dryRun = $this->option('dry-run');

    /** @var Subscription[]|Collection $subscriptions */
    $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->where('end_date', '<', now())
      ->limit($maxCount + 1)
      ->get();

    $moreItems = false;
    if ($subscriptions->count() > $maxCount) {
      $subscriptions->pop();
      $moreItems = true;
    }

    if ($subscriptions->count() <= 0) {
      Log::info('There is no subscriptions to process.');
      return Command::SUCCESS;
    }

    Log::info("Process {$subscriptions->count()} subscriptions ...");

    foreach ($subscriptions as $subscription) {
      Log::info("  stopping subscription: id=$subscription->id");
      if (!$dryRun) {
        // stop subscription
        $this->manager->stopSubscription($subscription, 'cancelled');

        // send notification
        $subscription->sendNotification(SubscriptionNotification::NOTIF_TERMINATED);
        usleep(1_000_000 / 20);
      }
    }

    Log::info("Process {$subscriptions->count()} subscriptions ... Done!");

    if ($moreItems) {
      Log::info('There are more subscriptions to process');
    }

    if (!$dryRun) {
      Log::info("Artisan: subscription:stop-cancelled: stop {$subscriptions->count()} cancelled subscriptions.");
    }

    return Command::SUCCESS;
  }
}
