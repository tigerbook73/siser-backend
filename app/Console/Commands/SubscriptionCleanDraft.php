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
    $maxCount = 100;
    $dryRun = $this->option('dry-run');

    /** @var Subscription[]|Collection $subscriptions */
    $subscriptions = Subscription::where('status', 'draft')
      ->where('created_at', '<', now()->subMinutes(30))
      ->limit($maxCount + 1)
      ->get();

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
        $this->manager->deleteSubscription($subscription);
        usleep(1_000_000 / 20);
      }
    }

    $this->info("Process {$subscriptions->count()} subscriptions ... Done!");

    if ($moreItems) {
      $this->info('There are more subscriptions to process');
    }

    Log::info("Artisan: subscription:clean-draft: clean {$subscriptions->count()} draft subscriptions.");
    return Command::SUCCESS;
  }
}
