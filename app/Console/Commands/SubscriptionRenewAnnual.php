<?php

namespace App\Console\Commands;

use App\Models\SubscriptionRenewal;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscriptionRenewAnnual extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:renew-annual';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Renew annual subscriptions';

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

    /**
     * steps:
     * 1. find gemany annual subscriptions that are going to renew in 30 days
     * 2. skip notified subscriptions
     * 3. skip subscriptions that has invoice in pending status
     * 4. notify customer to renew the subscriptions
     *
     * some others to do in other files:
     * 1. add flag for subscriptions that are notified
     * 2. add flag for subscriptions that are confirmed
     */


    Log::info('Artisan: subscription:renew-annual: start');

    /**
     * activate renewal
     */
    $renewals = SubscriptionRenewal::findPending();
    foreach ($renewals as $renewal) {
      $renewal->subscription->activatePendingRenewal();
    }

    /**
     * first notification
     */
    $renewals = SubscriptionRenewal::findToFirstReminder();
    foreach ($renewals as $renewal) {
      $subscription = $renewal->subscription;
      $subscription->updateActiveRenewalSubstatus(SubscriptionRenewal::SUB_STATUS_FIRST_REMINDERED);

      $subscription->sendNotification(SubscriptionNotification::NOTIF_RENEW_REQUIRED);
    }

    /**
     * final notification
     */
    $renewals = SubscriptionRenewal::findToFinalReminder();
    foreach ($renewals as $renewal) {
      $subscription = $renewal->subscription;
      $subscription->updateActiveRenewalSubstatus(SubscriptionRenewal::SUB_STATUS_FINAL_REMINDERED);

      $subscription->sendNotification(SubscriptionNotification::NOTIF_RENEW_REQUIRED);
    }

    /**
     * expire renewal
     */
    $renewals = SubscriptionRenewal::findToExpire();
    foreach ($renewals as $renewal) {
      $subscription = $renewal->subscription;
      $subscription->expireActiveRenewal();
      try {
        $this->manager->cancelSubscription($subscription, immediate: false);
      } catch (\Throwable $th) {
        Log::error('Error cancelling subscription: ' . $subscription->id . ' ' . $th->getMessage());
      }
    }

    return Command::SUCCESS;
  }
}
