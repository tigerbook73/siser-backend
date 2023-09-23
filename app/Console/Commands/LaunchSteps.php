<?php

namespace App\Console\Commands;

use App\Models\BillingInfo;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
      $this->info('  clear:             remove old data');
      $this->info('  init:              init data');
      $this->info('  update-countries:  update country list');
      $this->info('  update-plans:      update pro-plan');
      $this->info('  test:              test whether configure is ready');
      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'clear':
        return $this->clear();

      case 'init':
        return $this->init();

      case 'test':
        return $this->test();

      case 'update-countries':
        return $this->updateCountries();

      case 'update-plans':
        return $this->updatePlans();

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function clear()
  {
    if (config('dr.dr_mode') == 'prod') {
      $this->warn('This command can not be executed under "prod" mode');
      return self::FAILURE;
    }

    // clear dr information
    $this->info("--------------------------------------------");
    $this->call('dr:cmd', ['subcmd' => 'clear']);

    // disable hook
    $this->info("--------------------------------------------");
    $this->call('dr:cmd', ['subcmd' => 'disable-hook']);

    /**
     * table
     */
    $this->info("");
    $this->info("--------------------------------------------");
    TaxId::whereNotNull('id')->delete();
    BillingInfo::whereNotNull('id')->delete();
    PaymentMethod::whereNotNull('id')->delete();
    Invoice::whereNotNull('id')->delete();
    Subscription::where('subscription_level', '>', 1)->delete();

    // create subscription
    foreach (User::all() as $user) {
      $user->dr = null;
      $user->save();

      BillingInfo::createDefault($user);
      $user->updateSubscriptionLevel();
    }

    return self::SUCCESS;
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

  public function updateCountries()
  {
    $this->info("Update countries ...");

    $now = now();
    DB::table('countries')->upsert(
      [
        ['code' => 'AE', 'name' => 'United Arab Emirates',      'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AT', 'name' => 'Austria',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AU', 'name' => 'Australia',                 'currency' => 'AUD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'AW', 'name' => 'Andorra',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BE', 'name' => 'Belgium',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BG', 'name' => 'Bulgaria',                  'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BN', 'name' => 'Brunei Darussalam',         'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BR', 'name' => 'Brazil',                    'currency' => 'BRL', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'BS', 'name' => 'Bahamas',                   'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CA', 'name' => 'Canada',                    'currency' => 'CAD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CH', 'name' => 'Switzerland',               'currency' => 'CHF', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CL', 'name' => 'Chile',                     'currency' => 'CLP', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CO', 'name' => 'Columbia',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CR', 'name' => 'Costa Rica',                'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CY', 'name' => 'Cyprus',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'CZ', 'name' => 'Czechia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'DE', 'name' => 'Germany',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'DK', 'name' => 'Denmark',                   'currency' => 'DKK', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'DO', 'name' => 'Dominican Republic',        'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'EC', 'name' => 'Ecuador',                   'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'EE', 'name' => 'Estonia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ES', 'name' => 'Spain',                     'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'FI', 'name' => 'Finland',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'FR', 'name' => 'France',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GB', 'name' => 'Great Britain',             'currency' => 'GBP', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GF', 'name' => 'French Guiana',             'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GP', 'name' => 'Guadeloupe',                'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GR', 'name' => 'Greece',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'GT', 'name' => 'Guatemala',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'HN', 'name' => 'Honduras',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'HR', 'name' => 'Croatia',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'HU', 'name' => 'Hungary',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ID', 'name' => 'Indonesia',                 'currency' => 'IDR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IE', 'name' => 'Ireland',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IL', 'name' => 'Isreal',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IN', 'name' => 'India',                     'currency' => 'INR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IS', 'name' => 'Iceland',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'IT', 'name' => 'Italy',                     'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'JM', 'name' => 'Jamaica',                   'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'JP', 'name' => 'Japan',                     'currency' => 'JPY', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'KR', 'name' => 'Korea',                     'currency' => 'KRW', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LI', 'name' => 'Liechtenstein',             'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LT', 'name' => 'Lithuania',                 'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LU', 'name' => 'Luxembourg',                'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'LV', 'name' => 'Latvia',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MN', 'name' => 'Mongolia',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MT', 'name' => 'Malta',                     'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MU', 'name' => 'Mauritius',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MX', 'name' => 'Mexico',                    'currency' => 'MXN', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'MY', 'name' => 'Malaysia',                  'currency' => 'MYR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NI', 'name' => 'Nicaragua',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NL', 'name' => 'Netherlands',               'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NO', 'name' => 'Norway',                    'currency' => 'NOK', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'NZ', 'name' => 'New Zealand',               'currency' => 'NZD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PA', 'name' => 'Panama',                    'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PH', 'name' => 'Philippines',               'currency' => 'PHP', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PL', 'name' => 'Poland',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PM', 'name' => 'Saint Pierre and Miquelon', 'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PR', 'name' => 'Puerto Rico',               'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PT', 'name' => 'Portugal',                  'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'PY', 'name' => 'Paraguay',                  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'RE', 'name' => 'Reunion',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'RO', 'name' => 'Romania',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'RS', 'name' => 'Serbia',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SE', 'name' => 'Sweden',                    'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SG', 'name' => 'Singapore',                 'currency' => 'SGD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SI', 'name' => 'Slovenia',                  'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SK', 'name' => 'Slovakia',                  'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'SV', 'name' => 'El Salvador',               'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TH', 'name' => 'Thailand',                  'currency' => 'THB', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TR', 'name' => 'Turkiye',                   'currency' => 'TRY', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TT', 'name' => 'Trinidad and Tobago',       'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'TW', 'name' => 'Taiwan',                    'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'UA', 'name' => 'Ukraine',                   'currency' => 'EUR', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'US', 'name' => 'United States of America',  'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'VE', 'name' => 'Venezuela',                 'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'VI', 'name' => 'Virgin Islands',            'currency' => 'USD', 'created_at' => $now, 'updated_at' => $now],
        ['code' => 'ZA', 'name' => 'South Africa',              'currency' => 'ZAR', 'created_at' => $now, 'updated_at' => $now],
      ],
      ['code']
    );

    $this->info("Update countries ... Done!");
  }

  public function updatePlans()
  {
    $this->info("Update countries ...");

    DB::table('plans')->upsert(
      [
        [
          'name'                => 'Leonardo™ Design Studio Pro Monthly Plan',
          'product_name'        => 'Leonardo™ Design Studio Pro',
          'description'         => 'Leonardo™ Design Studio Pro Monthly Plan',
          'subscription_level'  => 2,
          'price_list'          => json_encode([
            ['country' => 'AE', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'AT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'AU', 'currency' => 'AUD', 'price' => 12.99],
            ['country' => 'AW', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'BE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'BG', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'BN', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'BR', 'currency' => 'BRL', 'price' => 39.99],
            ['country' => 'BS', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'CA', 'currency' => 'CAD', 'price' => 11.49],
            ['country' => 'CH', 'currency' => 'CHF', 'price' => 7.5],
            ['country' => 'CL', 'currency' => 'CLP', 'price' => 6999],
            ['country' => 'CO', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'CR', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'CY', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'CZ', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'DE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'DK', 'currency' => 'DKK', 'price' => 59.99],
            ['country' => 'DO', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'EC', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'EE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'ES', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'FI', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'FR', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'GB', 'currency' => 'GBP', 'price' => 6.99],
            ['country' => 'GF', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'GP', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'GR', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'GT', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'HN', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'HR', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'HU', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'ID', 'currency' => 'IDR', 'price' => 130000],
            ['country' => 'IE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'IL', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'IN', 'currency' => 'INR', 'price' => 700],
            ['country' => 'IS', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'IT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'JM', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'JP', 'currency' => 'JPY', 'price' => 1299],
            ['country' => 'KR', 'currency' => 'KRW', 'price' => 11699],
            ['country' => 'LI', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'LT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'LU', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'LV', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'MN', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'MT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'MU', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'MX', 'currency' => 'MXN', 'price' => 149],
            ['country' => 'MY', 'currency' => 'MYR', 'price' => 40],
            ['country' => 'NI', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'NL', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'NO', 'currency' => 'NOK', 'price' => 10.49],
            ['country' => 'NZ', 'currency' => 'NZD', 'price' => 14.49],
            ['country' => 'PA', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'PH', 'currency' => 'PHP', 'price' => 500],
            ['country' => 'PL', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'PM', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'PR', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'PT', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'PY', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'RE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'RO', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'RS', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SE', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SG', 'currency' => 'SGD', 'price' => 12.00],
            ['country' => 'SI', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SK', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'SV', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'TH', 'currency' => 'THB', 'price' => 300],
            ['country' => 'TR', 'currency' => 'TRY', 'price' => 235],
            ['country' => 'TT', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'TW', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'UA', 'currency' => 'EUR', 'price' => 7.99],
            ['country' => 'US', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'VE', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'VI', 'currency' => 'USD', 'price' => 8.99],
            ['country' => 'ZA', 'currency' => 'ZAR', 'price' => 169.99],
          ]),
          'url'                 => 'https://www.siserna.com/leonardo-design-studio/',
          'status'              => 'active',
          'created_at'          => now(),
          'updated_at'          => now(),
        ]
      ],
      ['name']
    );

    $this->info("Update countries ... Done!");
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

  public function createOrUpdateAnnualPlan()
  {
    /** @var Plan|null $annualPlan */
    $annualPlan = Plan::public()
      ->where('interval', Plan::INTERVAL_YEAR)
      ->where('interval_count', 1)
      ->first();

    if (!$annualPlan) {
      /** @var Plan $monthPlan */
      $monthPlan = Plan::where('name', 'Leonardo™ Design Studio Pro Monthly Plan')->first();
      $annualPlan = $monthPlan->replicate();
    }

    $annualPlan->name = 'Leonardo™ Design Studio Pro Annual Plan';
    $annualPlan->description = '1 year plan, will convert to "Leonardo™ Design Studio Pro Monthly Plan" after 1 year';
    $annualPlan->interval = Plan::INTERVAL_YEAR;
    $annualPlan->interval_count = 1;

    $price_list = $annualPlan->price_list;
    for ($i = 0; $i < count($price_list); $i++) {
      $price_list[$i]['price'] = $price_list[$i]['price'] * 10;
    }
    $annualPlan->price_list = $price_list;

    $annualPlan->save();
  }
}
