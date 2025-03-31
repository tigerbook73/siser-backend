<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Services\Paddle\SubscriptionManagerPaddle;
use App\Services\Paddle\SubscriptionService;
use App\Services\Paddle\TransactionService;
use Illuminate\Console\Command;

class TestCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'cmd:test';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  public function __construct()
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
    /**
     * only for local environment
     */
    if (!app()->environment('local')) {
      $this->error('This command is only available in local environment');
      return self::FAILURE;
    }

    /**
     * validate subscription
     */
    $this->info('');
    $this->info('validate subscription ...');
    $subscriptionBar = $this->output->createProgressBar(Subscription::count());
    $subscriptionBar->start();
    Subscription::chunkById(60, function ($subscriptions) use ($subscriptionBar) {
      /** @var \App\Models\Subscription[] $subscriptions */

      foreach ($subscriptions as $subscription) {
        // plan info
        $planInfo = $subscription->getPlanInfo();
        $subscription->setPlanInfo($planInfo);

        // license package info
        $licensePackageInfo = $subscription->getLicensePackageInfo();
        $subscription->setLicensePackageInfo($licensePackageInfo);

        // coupon info
        $couponInfo = $subscription->getCouponInfo();
        $subscription->setCouponInfo($couponInfo);

        $subscription->save();
        $subscriptionBar->advance();
      }
      $subscriptionBar->finish();
    });
    $this->info('');
    $this->info('subscription validation completed');

    /**
     * validate invoice
     */
    $this->info('');
    $this->info('validate invoice ...');
    $invoiceBar = $this->output->createProgressBar(Invoice::count());
    $invoiceBar->start();
    Invoice::chunkById(60, function ($invoices) use ($invoiceBar) {
      /** @var \App\Models\Invoice[] $invoices */

      foreach ($invoices as $invoice) {
        // plan info
        $planInfo = $invoice->getPlanInfo();
        $invoice->setPlanInfo($planInfo);

        // license package info
        $licensePackageInfo = $invoice->getLicensePackageInfo();
        $invoice->setLicensePackageInfo($licensePackageInfo);

        // coupon info
        $couponInfo = $invoice->getCouponInfo();
        $invoice->setCouponInfo($couponInfo);

        $invoice->save();
      }
      $invoiceBar->finish();
    });
    $this->info('');
    $this->info('invoice validation completed');

    return self::SUCCESS;
  }

  /**
   * refresh all subscriptions: shall be from from tinker only
   */
  public function refreshSubscriptions()
  {
    /** @var SubscriptionService $subscriptionService */
    $subscriptionService = app(SubscriptionManagerPaddle::class)->subscriptionService;
    Subscription::whereNotNull('meta->paddle->subscription_id')
      ->chunkById(60, function ($subscriptions) use ($subscriptionService) {
        /** @var \App\Models\Subscription[] $subscriptions */
        foreach ($subscriptions as $subscription) {
          $subscriptionService->refreshSubscription($subscription);
        }
      });
  }

  /**
   * refresh all invoices: shall be from from tinker only
   */
  public function refreshInvoices()
  {
    /** @var TransactionService $transactionService */
    $transactionService = app(SubscriptionManagerPaddle::class)->transactionService;
    Invoice::whereNotNull('meta->paddle->transaction_id')
      ->chunkById(60, function ($invoices) use ($transactionService) {
        /** @var \App\Models\Invoice[] $invoices */
        foreach ($invoices as $invoice) {
          $transactionService->refreshInvoice($invoice);
        }
      });
  }
}
