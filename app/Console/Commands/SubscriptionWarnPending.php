<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionWarning;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionWarnPending extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:warn-pending {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Warn pending subscriptions';

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

    /** @var int[] $subscriptionIds */
    $subscriptionIds = Subscription::select('id')
      // ->whereIn('status', ['pending', 'processing'])
      // ->where('updated_at', '<', now()->subMinutes(30))
      ->get()
      ->map(fn ($model) => $model->id)
      ->all();

    $this->info('There are ' . count($subscriptionIds) . ' pending or processing subscriptions: [' . implode(', ', $subscriptionIds) . '] !');

    if (!$dryRun && count($subscriptionIds) > 0) {
      SubscriptionWarning::notify('long-pending-subscription', $subscriptionIds);
    }

    return Command::SUCCESS;
  }
}
