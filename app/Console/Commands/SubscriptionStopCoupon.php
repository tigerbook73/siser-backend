<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionStopCoupon extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:stop-coupon {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'stop terminated subscriptions\' plans';

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
    $subscriptions = collect([]); // TODO: get subscriptions

    $moreItems = false;
    if ($subscriptions->count() > $maxCount) {
      $subscriptions->pop();
      $moreItems = true;
    }

    if ($subscriptions->count() <= 0) {
      $this->info('There is no subscriptions to process.');
      return Command::SUCCESS;
    }

    $this->info("Process {$subscriptions->count()} subscriptions ...");

    foreach ($subscriptions as $subscription) {
      $this->info("  deleting subscription: id=$subscription->id");
      if (!$dryRun) {
        // TODO: processing subscriptions
        usleep(1_000_000 / 20);
      }
    }

    $this->info("Process {$subscriptions->count()} subscriptions ... Done!");

    if ($moreItems) {
      $this->info('There are more items to process');
    }

    return Command::SUCCESS;
  }
}
