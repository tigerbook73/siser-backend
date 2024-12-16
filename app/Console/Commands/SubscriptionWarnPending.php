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

  /**
   * long period warning cooling periods
   */
  const INVOICE_PENDING_PERIOD        = '30 minutes';
  const INVOICE_RENEW_PERIOD          = '4 days';
  const INVOICE_PROCESSING_PERIOD     = '2 days';
  const REFUND_PROCESSING_PERIOD      = '3 days';
  const SUBSCRIPTION_HANGING_PERIOD   = '15 days';

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

    /**
     * Invoice in pending state for more than self::INVOICE_PENDING_PERIOD
     *
     * @var int[] $pendings - invoice ids in pending state
     */
    $pendings = Invoice::select('id')
      ->where('status', Invoice::STATUS_PENDING)
      ->where('type', Invoice::TYPE_NEW_SUBSCRIPTION)
      ->where('updated_at', '<', now()->sub(self::INVOICE_PENDING_PERIOD))
      ->limit($maxCount)
      ->get()
      ->map(fn($invoice) => $invoice->id)
      ->all();
    if (count($pendings) > 0) {
      Log::info('There are ' . count($pendings) . ' pending invoices: ' . implode(', ', $pendings) . ' !');
      $data['pending_invoice'] = $pendings;
    }

    /**
     * Invoice in renew state for more than self::INVOICE_RENEW_PERIOD
     *
     * @var int[] $renews - invoice ids in renew state
     */
    $renews = Invoice::select('id')
      ->where('status', Invoice::STATUS_PENDING)
      ->where('type', Invoice::TYPE_RENEW_SUBSCRIPTION)
      ->where('updated_at', '<', now()->sub(self::INVOICE_RENEW_PERIOD))
      ->limit($maxCount)
      ->get()
      ->map(fn($invoice) => $invoice->id)
      ->all();
    if (count($renews) > 0) {
      Log::info('There are ' . count($renews) . ' renew invoices: ' . implode(', ', $renews) . ' !');
      $data['renew_invoice'] = $renews;
    }

    /**
     * Invoice in processing state for more than self::INVOICE_PROCESSING_PERIOD
     *
     * @var Invoice[] $processingInvoices - invoice in processing state
     */
    $processingInvoices = Invoice::where('status', Invoice::STATUS_PROCESSING)
      ->where('updated_at', '<', now()->sub(self::INVOICE_PROCESSING_PERIOD))
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

    /**
     * Invoice in refunding state for more than self::REFUND_PROCESSING_PERIOD
     *
     * @var int[] $refundings - invoice ids in refunding state
     */
    $refundings = Invoice::select('id')
      ->where('status', Invoice::STATUS_REFUNDING)
      ->where('updated_at', '<', now()->sub(self::REFUND_PROCESSING_PERIOD))
      ->get()
      ->map(fn($model) => $model->id)
      ->all();
    if (count($refundings) > 0) {
      Log::info('There are ' . count($refundings) . ' refunding invoices: ' . implode(', ', $refundings) . ' !');
      $data['refunding_invoice'] = $refundings;
    }

    /**
     * Subscription is in active state but next invoice date has exceed its due date by over self::SUBSCRIPTION_HANGING_PERIOD
     *
     * @var int[] $subscriptions - subscription ids
     */
    $subscriptions = Subscription::select('id')
      ->where('status', Subscription::STATUS_ACTIVE)
      ->where('next_invoice_date', '<', now()->sub(self::SUBSCRIPTION_HANGING_PERIOD))
      ->get()
      ->map(fn($model) => $model->id)
      ->all();
    if (count($subscriptions) > 0) {
      Log::info('There are ' . count($subscriptions) . ' hanging subscriptions: ' . implode(', ', $subscriptions) . ' !');
      $data['hanging_subscription'] = $subscriptions;
    }

    if (!$dryRun && count($data) > 0) {
      SubscriptionWarning::notify(SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION, $data);
    }

    return Command::SUCCESS;
  }
}
