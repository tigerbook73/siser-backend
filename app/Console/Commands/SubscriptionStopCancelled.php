<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

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
    $maxCount = 30;
    $dryRun = $this->option('dry-run');

    /** @var Subscription[]|Collection $subscriptions */
    $subscriptions = Subscription::where('status', 'active')
      ->where('sub_status', 'cancelling')
      ->where('current_period_end_date', '<', now())
      ->limit($maxCount + 1)
      ->get();

    $moreItems = false;
    if ($subscriptions->count() > $maxCount) {
      $subscriptions->pop();
      $moreItems = true;
    }

    if ($subscriptions->count() <= 0) {
      $this->info('There is no cancelling subscription to stop.');
      return Command::SUCCESS;
    }

    $this->info("Clear {$subscriptions->count()} draft subscriptions ...");

    foreach ($subscriptions as $subscription) {
      $this->info("  Stopping subscription: id=$subscription->id");
      if (!$dryRun) {
        // stop subscription data
        $subscription->stop('stopped', 'cancelled');

        // activate default subscription
        $user = $subscription->user;
        if ($user->machines()->count() > 0) {
          $basicSubscription = Subscription::createBasicMachineSubscription($user);
          $user->subscription_level = $basicSubscription->subscription_level;
        } else {
          $user->subscription_level = 0;
        }
        $user->save();

        // send notification
        $subscription->sendNotification(SubscriptionNotification::NOTIF_TERMINATED);
        usleep(1_000_000 / 20);
      }
    }

    $this->info("Clear {$subscriptions->count()} draft subscriptions ... Done!");

    if ($moreItems) {
      $this->info('There are more items to process');
    }

    return Command::SUCCESS;
  }
}
