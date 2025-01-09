<?php

namespace App\Console\Commands;

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
  protected $signature = 'subscription:warn-pending';

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

    $data = [];


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

    if (count($data) > 0) {
      SubscriptionWarning::notify(SubscriptionWarning::NOTIF_LONG_PENDING_SUBSCRIPTION, $data);
    }

    return Command::SUCCESS;
  }
}
