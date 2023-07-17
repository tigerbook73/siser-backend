<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SubscriptionCleanDraft extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:clean-draft {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'clean draft subscriptions';

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
    Log::info('Artisan: subscription:clean-draft: start');

    $maxCount = 100;
    $dryRun = $this->option('dry-run');

    /** @var Subscription[]|Collection $subscriptions */
    $subscriptions = Subscription::where('status', Subscription::STATUS_DRAFT)
      ->where('created_at', '<', now()->subMinutes(30))
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
      Log::info("  deleting subscription: id=$subscription->id");
      if (!$dryRun) {
        $this->manager->deleteSubscription($subscription);
        usleep(1_000_000 / 20);
      }
    }

    Log::info("Process {$subscriptions->count()} subscriptions ... Done!");

    if ($moreItems) {
      Log::info('There are more subscriptions to process');
    }

    if (!$dryRun) {
      Log::info("Artisan: subscription:clean-draft: clean {$subscriptions->count()} draft subscriptions.");
    }

    return Command::SUCCESS;
  }
}
