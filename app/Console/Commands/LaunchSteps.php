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
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManager;
use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\UpdateSubscriptionRequest;
use DigitalRiver\ApiSdk\ObjectSerializer as DrObjectSerializer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    $now = now();
    DB::table('countries')->upsert(
      [
        ['code' => 'AD', 'name' => 'Andorra',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AL', 'name' => 'Albania',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AM', 'name' => 'Armenia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BA', 'name' => 'Bosnia And Herzegovina',    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ME', 'name' => 'Montenegro',                'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MK', 'name' => 'Macedonia',                 'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
      ],
      ['code'],
    );
  }

  public function updatePlans()
  {
    $countryData = [
      'AD' => ['code' => 'AD', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'AL' => ['code' => 'AL', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'AM' => ['code' => 'AM', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'AT' => ['code' => 'AT', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'BA' => ['code' => 'BA', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'BE' => ['code' => 'BE', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'BG' => ['code' => 'BG', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'CY' => ['code' => 'CY', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'CZ' => ['code' => 'CZ', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'DE' => ['code' => 'DE', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'DK' => ['code' => 'DK', 'currency' =>  'DKK', 'month_amount' => 59.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'EE' => ['code' => 'EE', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'ES' => ['code' => 'ES', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'FI' => ['code' => 'FI', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'FR' => ['code' => 'FR', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'GP' => ['code' => 'GP', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'GR' => ['code' => 'GR', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'HR' => ['code' => 'HR', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'HU' => ['code' => 'HU', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'IE' => ['code' => 'IE', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'IL' => ['code' => 'IL', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'IS' => ['code' => 'IS', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'IT' => ['code' => 'IT', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'LI' => ['code' => 'LI', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'LT' => ['code' => 'LT', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'LU' => ['code' => 'LU', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'LV' => ['code' => 'LV', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'ME' => ['code' => 'ME', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'MK' => ['code' => 'MK', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'MT' => ['code' => 'MT', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'NL' => ['code' => 'NL', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'NO' => ['code' => 'NO', 'currency' =>  'NOK', 'month_amount' => 10.49, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'PL' => ['code' => 'PL', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'PM' => ['code' => 'PM', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'PT' => ['code' => 'PT', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'RE' => ['code' => 'RE', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'RO' => ['code' => 'RO', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'RS' => ['code' => 'RS', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'SE' => ['code' => 'SE', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'SI' => ['code' => 'SI', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'SK' => ['code' => 'SK', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
      'UA' => ['code' => 'UA', 'currency' =>  'EUR', 'month_amount' => 7.99, 'tax_rate' => 1.0, 'month_price' => 0.0, 'year_amount' => 0.0, 'year_price' => 0.0],
    ];

    /** @var Plan|null $annualPlan */
    $annualPlan = Plan::public()
      ->where('interval', Plan::INTERVAL_YEAR)
      ->where('interval_count', 1)
      ->first();

    /** @var Plan $monthPlan */
    $monthPlan = Plan::public()
      ->where('interval', Plan::INTERVAL_MONTH)
      ->where('interval_count', 1)
      ->first();

    $rawRequest = [
      "currency" => "USD",
      "email" => "user1.test@iifuture.com",
      "billTo" => [
        "address" => [
          "country" => "AU"
        ],
      ],
      "items" => [
        [
          "productDetails" => [
            "skuGroupId" => "software-subscription-01",
            "name" => "Leonardo™ Design Studio Pro Monthly Plan"
          ],
          "subscriptionInfo" => [
            "freeTrial" => false,
            "autoRenewal" => true,
            "terms" => "These are the terms...",
            "planId" => "standard-1-month"
          ],
          "price" => 100
        ]
      ],
      "taxInclusive" => false,
      "chargeType" => "customer_initiated",
      "customerType" => "individual"
    ];
    /** @var DrCheckoutRequest $checkoutRequest */
    $checkoutRequest = DrObjectSerializer::deserialize(json_encode($rawRequest), DrCheckoutRequest::class);

    // retrieve tax rate
    foreach ($countryData as $code => $data) {
      $checkoutRequest->setCurrency($data['currency']);
      $checkoutRequest->getBillTo()->getAddress()->setCountry($code);

      $checkout = $this->drService->checkoutApi->createCheckouts($checkoutRequest);
      $taxRate = $checkout->getItems()[0]->getTax()->getRate();
      $countryData[$code]['tax_rate'] = $taxRate;
      $this->drService->subscriptionApi->deleteSubscriptions($checkout->getItems()[0]->getSubscriptionInfo()->getSubscriptionId());
      $this->drService->checkoutApi->deleteCheckouts($checkout->getId());
      printf("code: %s, tax_rate: %4s, country: %s\n", $code, (string)$taxRate, Country::findByCode($code)->name);
    }

    // update annual amount
    foreach ($countryData as $code => $data) {
      $countryData[$code]['year_amount'] = floor($countryData[$code]['month_amount'] * 12 * 0.9);
    }

    // update monthly plan
    $price_list = array_merge(
      $monthPlan->price_list,
      [
        ['country' => 'AD', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'AL', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'AM', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'BA', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'ME', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'MK', 'currency' => 'EUR', 'price' => 7.99],
      ]
    );
    for ($i = 0; $i < count($price_list); $i++) {
      $data = $countryData[$price_list[$i]['country']] ?? null;
      if (!$data) {
        continue;
      }

      $price_list[$i]['price'] = round($data['month_amount'] / (1 + $data['tax_rate']), 2);
      $price_list[$i]['currency'] = $data['currency'];
    }
    array_multisort(array_column($price_list, 'country'), SORT_ASC, $price_list);
    $monthPlan->price_list = $price_list;
    $monthPlan->save();

    // update annual plan
    $price_list = array_merge(
      $annualPlan->price_list,
      [
        ['country' => 'AD', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'AL', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'AM', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'BA', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'ME', 'currency' => 'EUR', 'price' => 7.99],
        ['country' => 'MK', 'currency' => 'EUR', 'price' => 7.99],
      ]
    );
    for ($i = 0; $i < count($price_list); $i++) {
      $data = $countryData[$price_list[$i]['country']] ?? null;
      if (!$data) {
        continue;
      }

      $price_list[$i]['price'] = round($data['year_amount'] / (1 + $data['tax_rate']), 2);
      $price_list[$i]['currency'] = $data['currency'];
    }
    array_multisort(array_column($price_list, 'country'), SORT_ASC, $price_list);
    $annualPlan->price_list = $price_list;
    $annualPlan->save();
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
}
