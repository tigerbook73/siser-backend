<?php

namespace App\Console\Commands;

use App\Models\Invoice;
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
    $data = [];

    /** @var int[] $pendings - invoice ids in pending state */
    $pendings = Invoice::select('id')
      ->where('status', Invoice::STATUS_PENDING)
      ->where('period', 0)
      ->where('updated_at', '<', now()->sub(SubscriptionWarning::INVOICE_PENDING_PERIOD))
      ->limit($maxCount)
      ->get()
      ->map(fn ($invoice) => $invoice->id)
      ->all();
    if (count($pendings) > 0) {
      Log::info('There are ' . count($pendings) . ' pending invoices: ' . implode(', ', $pendings) . ' !');
      $data['pending_invoice'] = $pendings;
    }

    /** @var int[] $renews - invoice ids in renew state */
    $renews = Invoice::select('id')
      ->where('status', Invoice::STATUS_PENDING)
      ->where('period', '>', 1)
      ->where('updated_at', '<', now()->sub(SubscriptionWarning::INVOICE_RENEW_PERIOD))
      ->limit($maxCount)
      ->get()
      ->map(fn ($invoice) => $invoice->id)
      ->all();
    if (count($renews) > 0) {
      Log::info('There are ' . count($renews) . ' renew invoices: ' . implode(', ', $renews) . ' !');
      $data['renew_invoice'] = $renews;
    }

    /** @var Invoice[] $processingInvoices - invoice in processing state  */
    $processingInvoices = Invoice::where('status', Invoice::STATUS_PROCESSING)
      ->where('updated_at', '<', now()->sub(SubscriptionWarning::INVOICE_PROCESSING_PERIOD))
      ->limit($maxCount)
      ->get();
    /** @var int[] $processings */
    $processings = [];
    foreach ($processingInvoices as $invoice) {
      // try to complete the invoice
      if ($dryRun || !$this->manager->tryCompleteInvoice($invoice)) {
        $processings[] = $invoice->id;
      }
    }
    if (count($processings) > 0) {
      Log::info('There are ' . count($processings) . ' processing invoices: ' . implode(', ', $processings) . ' !');
      $data['processing_invoice'] = $processings;
    }

    /** @var int[] $refundings - invoice ids in refunding state */
    $refundings = Invoice::select('id')
      ->where('status', Invoice::STATUS_REFUNDING)
      ->where('updated_at', '<', now()->sub(SubscriptionWarning::REFUND_PROCESSING_PERIOD))
      ->get()
      ->map(fn ($model) => $model->id)
      ->all();
    if (count($refundings) > 0) {
      Log::info('There are ' . count($refundings) . ' refunding invoices: ' . implode(', ', $refundings) . ' !');
      $data['refunding_invoice'] = $refundings;
    }

    if (!$dryRun && count($data) > 0) {
      SubscriptionWarning::notify(SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION, $data);
    }

    return Command::SUCCESS;
  }
}
