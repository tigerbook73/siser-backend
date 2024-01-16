<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Notifications\SubscriptionWarning;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    Log::info('Artisan: subscription:warn-pending: start');

    $maxCount = 100;
    $dryRun = $this->option('dry-run');

    /** @var int[] $pendings */
    $pendings = Subscription::select('id')
      ->where('status', Subscription::STATUS_PENDING)
      ->where('updated_at', '<', now()->subMinutes(30))
      ->get()
      ->map(fn ($model) => $model->id)
      ->all();

    /** @var Invoice[] $invoices */
    $invoices = Invoice::where('status', Invoice::STATUS_PROCESSING)
      ->where('updated_at', '<', now()->subDays(2))
      ->get();

    /** @var int[] $processings */
    $processings = [];
    foreach ($invoices as $invoice) {
      // try to complete the invoice
      if ($dryRun || !$this->manager->tryCompleteInvoice($invoice)) {
        $processings[] = $invoice->subscription_id;
      }
    }

    /** @var int[] $refundings */
    $refundings = Invoice::select('subscription_id')
      ->where('status', Invoice::STATUS_REFUNDING)
      ->where('updated_at', '<', now()->subDays(3))
      ->get()
      ->map(fn ($model) => $model->subscription_id)
      ->all();

    $subscriptionIds = array_merge($pendings, $processings, $refundings);
    Log::info('There are ' . count($subscriptionIds) . ' pending or processing subscriptions: [' . implode(', ', $subscriptionIds) . '] !');

    if (!$dryRun && count($subscriptionIds) > 0) {
      SubscriptionWarning::notify(SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION, $subscriptionIds);
    }

    return Command::SUCCESS;
  }
}
