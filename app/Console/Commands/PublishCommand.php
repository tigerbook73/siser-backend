<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Console\Command;
use App\Console\Commands\LastRecord;

class PublishCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'publish:cmd
                          {subcmd=help : subcommand}
                          {--dry-run   : do not save changes to the database}
                          {--force}
                          ';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'The command is for update and validate data after publishing a new release.';

  /**
   * options
   */
  protected $dryRun = false;
  protected $force = false;


  public function __construct(
    public SubscriptionManagerPaddle $manager,
  ) {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $this->dryRun = $this->option('dry-run');
    $this->force = $this->option('force');

    $subcmd = $this->argument('subcmd');

    if ($subcmd === 'help') {
      $this->showHelp();
      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'validate-subscriptions':
        $this->validateSubscriptions();
        break;

      case 'validate-invoices':
        $this->validateInvoices();
        break;

      case 'refresh-subscriptions':
        $this->refreshSubscriptions();
        break;

      case 'refresh-invoices':
        $this->refreshInvoices();
        break;

      default:
        $this->error('Unknown subcommand: ' . $subcmd);
        return self::FAILURE;
    }

    return self::SUCCESS;
  }

  public function showHelp()
  {
    $this->info('Usage: php artisan publish:cmd {subcmd} {--dry-run} {--force}');
    $this->info('');
    $this->info('subcmd:');
    $this->info('  validate-subscriptions   Validate all subscriptions');
    $this->info('  validate-invoices        Validate all invoices');
    $this->info('  refresh-subscriptions    Refresh all subscriptions');
    $this->info('  refresh-invoices         Refresh all invoices');
    $this->info('');
    $this->info('Options:');
    $this->info('  --dry-run                Do not save changes to the database');
    $this->info('  --force                  Force the command to run without confirmation');
  }


  /**
   * validate all subscriptions
   */
  public function validateSubscriptions()
  {
    /**
     * validate subscription
     */
    $this->info('');
    $this->info('validate subscription ...');

    $lastRecord = new LastRecord(__FUNCTION__, $this->force);
    $query = Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('subscription_level', 2)
      ->where('id', '>', $lastRecord->getLast());

    $progressBar = $this->output->createProgressBar($query->count());
    $progressBar->start();
    $query->chunkById(60, function ($subscriptions) use ($progressBar, $lastRecord) {
      /** @var \App\Models\Subscription[] $subscriptions */
      foreach ($subscriptions as $subscription) {
        // validate subscription
        $this->validateSubscription($subscription);
        $lastRecord->setLast($subscription->id);
        $progressBar->advance();
      }
    });
    $progressBar->finish();

    $this->info('');
    $this->info('subscription validation completed');
  }

  /**
   * validate and fix subscription
   */
  public function validateSubscription(Subscription $subscription): void
  {
    $billingInfo = $subscription->getBillingInfo();
    $subscription->setBillingInfo($billingInfo);

    $paymentMethodInfo = $subscription->getPaymentMethodInfo();
    $subscription->setPaymentMethodInfo($paymentMethodInfo);

    $planInfo = $subscription->getPlanInfo();
    $subscription->setPlanInfo($planInfo);

    $licensePackageInfo = $subscription->getLicensePackageInfo();
    $subscription->setLicensePackageInfo($licensePackageInfo);

    $items = $subscription->getItems();
    $subscription->setItems($items);

    $nextInvoice = $subscription->getNextInvoice();
    $subscription->setNextInvoice($nextInvoice);

    $couponInfo = $subscription->getCouponInfo();
    $subscription->setCouponInfo($couponInfo);

    $meta = $subscription->getMeta();
    $subscription->setMeta($meta);


    if (!$this->dryRun) {
      $subscription->save();
    }
  }

  /**
   * validate all invoices
   */
  public function validateInvoices()
  {
    $this->info('');
    $this->info('validate invoices ...');

    $lastRecord = new LastRecord(__FUNCTION__, $this->force);
    $query = Invoice::where('id', '>', $lastRecord->getLast());

    $progressBar = $this->output->createProgressBar($query->count());
    $progressBar->start();
    $query->chunkById(60, function ($invoices) use ($progressBar, $lastRecord) {
      /** @var \App\Models\Invoice[] $invoices */
      foreach ($invoices as $invoice) {
        $this->validateInvoice($invoice);
        $lastRecord->setLast($invoice->id);
        $progressBar->advance();
      }
    });
    $progressBar->finish();

    $this->info('');
    $this->info('invoices validation completed');
  }

  /**
   * validate and fix invoice
   */
  public function validateInvoice(Invoice $invoice): void
  {
    $billingInfo = $invoice->getBillingInfo();
    $invoice->setBillingInfo($billingInfo);

    $paymentMethodInfo = $invoice->getPaymentMethodInfo();
    $invoice->setPaymentMethodInfo($paymentMethodInfo);

    $planInfo = $invoice->getPlanInfo();
    $invoice->setPlanInfo($planInfo);

    $licensePackageInfo = $invoice->getLicensePackageInfo();
    $invoice->setLicensePackageInfo($licensePackageInfo);

    $items = $invoice->getItems();
    $invoice->setItems($items);

    $couponInfo = $invoice->getCouponInfo();
    $invoice->setCouponInfo($couponInfo);

    $meta = $invoice->getMeta();
    $invoice->setMeta($meta);

    if (!$this->dryRun) {
      $invoice->save();
    }
  }

  /**
   * refresh all subscriptions
   */
  public function refreshSubscriptions()
  {
    $this->info('');
    $this->info("refresh all subscriptions ...\n");

    $lastRecord = new LastRecord(__FUNCTION__, $this->force);
    $query = Subscription::whereNotNull('meta->paddle->subscription_id')
      ->where('id', '>', $lastRecord->getLast());

    $progressBar = $this->output->createProgressBar($query->count());
    $progressBar->start();
    $query->chunkById(60, function ($subscriptions) use ($progressBar, $lastRecord) {
      /** @var \App\Models\Subscription[] $subscriptions */
      foreach ($subscriptions as $subscription) {
        if (!$this->dryRun) {
          $this->manager->subscriptionService->refreshSubscription($subscription);
        }
        $lastRecord->setLast($subscription->id);
        $progressBar->advance();
      }
    });
    $progressBar->finish();

    $this->info('');
    $this->info('refresh subscriptions completed');
  }

  /**
   * refresh all invoices: shall be from from tinker only
   */
  public function refreshInvoices()
  {
    $this->info('');
    $this->info("refresh all invoices ...\n");

    $lastRecord = new LastRecord(__FUNCTION__, $this->force);
    $query = Invoice::whereNotNull('meta->paddle->transaction_id')
      ->where('id', '>', $lastRecord->getLast());

    $progressBar = $this->output->createProgressBar($query->count());
    $progressBar->start();
    $query->chunkById(60, function ($invoices) use ($progressBar, $lastRecord) {
      /** @var \App\Models\Invoice[] $invoices */
      foreach ($invoices as $invoice) {
        if (!$this->dryRun) {
          $this->manager->transactionService->refreshInvoice($invoice);
        }
        $lastRecord->getLast();
        $progressBar->advance();
      }
    });
    $progressBar->finish();

    $this->info('');
    $this->info('refresh invoices completed');
  }

  /**
   * refresh all products and prices with latest CustomData
   */
}
