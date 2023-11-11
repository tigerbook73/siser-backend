<?php

namespace App\Console\Commands;

use App\Models\BillingInfo;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManager;
use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest;
use DigitalRiver\ApiSdk\ObjectSerializer as DrObjectSerializer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceBeautify
{
  public static function beautifyToInt(float $in): float
  {
    if ($in < 50) {
      return self::roundUnder50($in);
    }
    return self::round50AndGreater($in);
  }


  /**
   * the last digit 
   * 0 - Round down to 9
   * 1 - Round down to 9
   * 2 - Round down to 9
   * 3 - Round down to 9
   * 4 - Round down to 9
   * 5 - Leave at 5
   * 6 - Round down to 5
   * 7 - Round down to 5
   * 8 - Round down to 5
   * 9 - Leave at 9
   */
  public static function round50AndGreater(float $in): float
  {
    $result    = round($in);
    $lastDigit = $result % 10;
    $result    = floor($result / 10) * 10;

    if ($lastDigit <= 4) {
      $result = $result - 1;
    } elseif ($lastDigit <= 8) {
      $result = $result + 5;
    } else {
      $result = $result + 9;
    }

    return $result;
  }


  /**
   * 0 - Leave at 0
   * 1 - Round down to 0
   * 2 - Leave at 2
   * 3 - Leave at 3
   * 4 - Leave at 4
   * 5 - Leave at 5
   * 6 - Round down to 5
   * 7 - Leave at 7
   * 8 - Leave at 8
   * 9 - Leave at 9
   */
  public static function roundUnder50(float $in): float
  {
    $result = round($in);
    if ($result < 2) {

      return $result;
    }

    $lastDigit = $result % 10;

    if ($lastDigit === 1) {
      $result = $result - 1;
    } elseif ($lastDigit === 6) {
      $result = $result - 1;
    }

    return $result;
  }
}


class LaunchSteps extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'launch:step {subcmd=help}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'steps to launch online-store';


  public function __construct(
    public SubscriptionManager $manager,
    public DigitalRiverService $drService
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
    /**
     * setup steps
     * from portal
     * 1. create webhook & retrieve keys
     * 2. 
     * 1. update dr public key
     * 2. update dr confidential key
     * 3. 
     */
    $subcmd = $this->argument('subcmd');
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan dr:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  init:              init data');
      $this->info('  launch:            launch new release (shall be updated every release)');
      $this->info('  update-countries:  update country list');
      $this->info('  update-plans:      update pro-plan');
      $this->info('  test:              test whether configure is ready');
      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'init':
        return $this->init();

      case 'launch':
        return $this->launch();

      case 'test':
        return $this->test();

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function init()
  {
    if (config('dr.dr_mode') == 'prod') {
      $this->warn('This command can not be executed under "prod" mode');
      return self::FAILURE;
    }

    // init plan
    $this->call('dr:cmd', ['subcmd' => 'init']);

    // enable hook
    $this->call('dr:cmd', ['subcmd' => 'enable-hook']);
  }

  public function launch()
  {
    $this->updateCountries();

    $this->updatePlans();
  }


  public function test()
  {
    // TODO: test whether configure is ready

    // 0. check dr mode
    printf('Check DR mode ................ ' . config('dr.dr_mode'));

    // 1. check token
    printf('Check DR Token ............... ');

    // 2. check plan
    // 3. check sku group
    // 4. check webhook
  }

  public function updateCountries()
  {
    // $now = now();
    // DB::table('countries')->upsert(
    //   [
    //     ['code' => 'AD', 'name' => 'Andorra',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
    //   ],
    //   ['code'],
    // );
  }

  public function updatePlans()
  {
  }


  /**
   * Update the timezone for users who have a null timezone value to UTC.
   *
   * @return void
   */
  static public function updateTimeZone()
  {
    User::whereNull('timezone')->update(['timezone' => 'UTC']);
  }

  static public function fixSubscriptionNextInvoiceTotalAmount()
  {
    Subscription::where('status', 'active')
      ->where('subscription_level', 2)
      ->chunkById(100, function ($subscriptions) {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
          $next_invoice = $subscription->next_invoice;
          if ($next_invoice) {
            $next_invoice['total_amount'] = $next_invoice['total_tax'] + $next_invoice['subtotal'];
            $subscription->next_invoice = $next_invoice;
            $subscription->save();
          }
        }
      });
  }

  static function fixSubscriptionNextInvoiceCurrentPeriod()
  {
    /** @var Subscription[] $subscriptions */
    $subscriptions = Subscription::where('status', 'active')
      ->where('sub_status', '<>', 'cancelling')
      ->where('subscription_level', 2)
      ->whereNotNull('next_invoice')
      ->get();

    $s = [];
    foreach ($subscriptions as $subscription) {
      if ($subscription->next_invoice && !isset($subscription->next_invoice['current_period'])) {
        $next_invoice = $subscription->next_invoice;
        $next_invoice['current_period'] = $subscription->current_period + 1;
        $subscription->next_invoice = $next_invoice;
        $subscription->save();

        $s[] = $subscription;
      }
    }

    return $s;
  }

  /**
   * run: LaunchSteps::cleanOldPreCalculateTaxCheckouts()
   */
  static public function cleanOldPreCalculateTaxCheckouts()
  {
    $maxCount = 100;

    $drService = new DigitalRiverService();

    try {
      Log::info('Clean old tax-calculation checkouts and subscriptions: start');

      $response =  $drService->subscriptionApi->listSubscriptions(state: 'draft', limit: $maxCount);
      $drSubscriptions = $response->getData();
      $count = 0;
      foreach ($drSubscriptions as $drSubscription) {
        $response =  $drService->checkoutApi->listCheckouts(subscription_id: $drSubscription->getId(), limit: $maxCount);
        $drCheckouts = $response->getData();
        if (count($drCheckouts) != 1) {
          continue;
        }
        $drCheckout = $drCheckouts[0];

        if (($drCheckout->getItems()[0]->getMetadata()['subscription_id'] ?? null) == '9999999') {
          $count++;
          $drService->checkoutApi->deleteCheckouts($drCheckout->getId());
          $drService->subscriptionApi->deleteSubscriptions($drSubscription->getId());
          printf(".");
        }
      }
      printf("\n");
      Log::info("Clean old tax-calculation checkouts and subscriptions: clean $count draft subscriptions & checkouts.");
    } catch (\Throwable $th) {
      //throw $th;
      Log::info($th->getMessage());
    }
  }

  static public function updateAnnualPlans()
  {
    $drService = new DigitalRiverService();

    /**
     * 1. find active annual subscriptions
     * 2. for each subscription
     *    A. update subscription's next plan
     *    B. update subscription's dr-subscription
     *    C. create subscription renewal (german only)
     *    D. send notification (update plan)
     */


    /** @var Subscription[] $subscriptions */
    $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
      ->where('sub_status', '<>', Subscription::SUB_STATUS_CANCELLING)
      ->where('subscription_level', 2)
      ->where('plan_info->interval', Plan::INTERVAL_YEAR)
      ->get();

    foreach ($subscriptions as $subscription) {
      try {
        if ($subscription->isFreeTrial()) {
          continue;
        }

        //code...
        $drSubscription = $drService->getSubscription($subscription->dr_subscription_id);

        // A. update subscription's next plan
        $subscription->fillNextInvoice();
        $subscription->save();

        // B. update subscription's dr-subscription
        $drService->convertSubscriptionToNext($drSubscription, $subscription);

        Log::info("Update subscription {$subscription->id} next plan to {$subscription->plan_info['name']}");

        // C. create subscription renewal (german only)
        $renewal = $subscription->createRenewal();

        // D. send notification (update plan)
        if (isset($renewal)) {
          $subscription->sendNotification(SubscriptionNotification::NOTIF_PLAN_UPDATED_GERMAN);
        } else {
          $subscription->sendNotification(SubscriptionNotification::NOTIF_PLAN_UPDATED_OTHER);
        }
      } catch (\Throwable $th) {
        Log::warning("Update subscription {$subscription->id} failed: {$th->getMessage()}");
      }
    }
  }

  static public function fixTaxRateAndTaxAmount()
  {
    $drService = new DigitalRiverService();

    $count = 0;
    Subscription::whereNotNull('next_invoice')
      ->chunkById(100, function ($subscriptions) use (&$count, $drService) {
        /** @var Subscription $subscription*/
        foreach ($subscriptions as $subscription) {
          $origin = [
            'tax_rate' => $subscription->tax_rate,
            'total_tax' => $subscription->total_tax,
            'total_amount' => $subscription->total_amount,
          ];
          $originalNext = $subscription->next_invoice;
          $next_invoice = $subscription->next_invoice;

          /**
           * update current period
           */

          /** @var Invoice $invoice */
          $invoice = $subscription->invoices()->where('period', $subscription->current_period)->first();
          $drOrder = $drService->getOrder($invoice->getDrOrderId());
          // update tax rate => 0 if total_tax is 0
          $subscription->tax_rate = ($drOrder->getSubtotal() != 0 && $drOrder->getTotalTax() == 0) ? 0 : $drOrder->getItems()[0]->getTax()->getRate();

          /**
           * update next invoice
           */

          /** @var Invoice|null $nextInvoice */
          $nextInvoice = $subscription->invoices()->where('period', $subscription->next_invoice['current_period'])->first();
          if ($nextInvoice) {
            $drInvoice = $drService->getInvoice($nextInvoice->getDrInvoiceId());
            // update tax rate => 0 if total_tax is 0
            $tax_rate = ($drInvoice->getSubtotal() != 0 && $drInvoice->getTotalTax() == 0) ? 0 : $drInvoice->getItems()[0]->getTax()->getRate();
            if ($next_invoice['tax_rate'] != $tax_rate) {
              $next_invoice['tax_rate'] = $tax_rate;
              $subscription->next_invoice = $next_invoice;
            }
          } else {
            // tax rate should be same as this period
            if ($next_invoice['tax_rate'] != $subscription->tax_rate) {
              $next_invoice['tax_rate'] = $subscription->tax_rate;
              $subscription->next_invoice = $next_invoice;
            }

            if ($next_invoice['subtotal'] == $subscription->subtotal) {
              // total tax and total amount should be same as this period
              if (
                $next_invoice['total_tax'] != $subscription->total_tax ||
                $next_invoice['total_amount'] != $subscription->total_amount
              ) {
                $next_invoice['total_tax'] = $subscription->total_tax;
                $next_invoice['total_amount'] = $subscription->total_amount;
                $subscription->next_invoice = $next_invoice;
              }
            } else {
              // recalc total tax and total amount
              $total_tax = round($next_invoice['subtotal'] * $next_invoice['tax_rate'], 2);
              if ($next_invoice['total_tax'] != $total_tax) {
                $next_invoice['total_tax'] = $total_tax;
                $next_invoice['total_amount'] = round($next_invoice['subtotal'] + $total_tax, 2);
                $subscription->next_invoice = $next_invoice;
              }
            }
          }

          // update 
          if ($subscription->isDirty()) {
            printf("Subscription %d updated:\n", $subscription->id);
            printf("  %-20s: %f -> %f %s\n", 'tax_rate',  $origin['tax_rate'], $subscription->tax_rate, $origin['tax_rate'] == $subscription->tax_rate ? '' : '*');
            printf("  %-20s: %f -> %f %s\n", 'total_tax', $origin['total_tax'], $subscription->total_tax, $origin['total_tax'] == $subscription->total_tax ? '' : '*');
            printf("  %-20s: %f -> %f %s\n", 'total_amount', $origin['total_amount'], $subscription->total_amount, $origin['total_amount'] == $subscription->total_amount ? '' : '*');
            printf("  %-20s: %f -> %f %s\n", 'next_tax_rate',  $originalNext['tax_rate'], $next_invoice['tax_rate'], $originalNext['tax_rate'] == $next_invoice['tax_rate'] ? '' : '*');
            printf("  %-20s: %f -> %f %s\n", 'next_total_tax', $originalNext['total_tax'], $next_invoice['total_tax'], $originalNext['total_tax'] == $next_invoice['total_tax'] ? '' : '*');
            printf("  %-20s: %f -> %f %s\n", 'next_total_amount', $originalNext['total_amount'], $next_invoice['total_amount'], $originalNext['total_amount'] == $next_invoice['total_amount'] ? '' : '*');
            printf("\n");

            $subscription->save();
            $count++;
          }
        }
      });

    printf("Total %d subscriptions updated.\n", $count);
  }
}
