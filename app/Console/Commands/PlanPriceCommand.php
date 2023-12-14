<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use Illuminate\Console\Command;

use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\Address as DrAddress;
use DigitalRiver\ApiSdk\Model\Billing as DrBilling;
use DigitalRiver\ApiSdk\Model\SkuRequestItem as DrSkuRequestItem;
use DigitalRiver\ApiSdk\Model\ProductDetails as DrProductDetails;

class PlanPriceCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'plan:price {subcmd=eu-tax} {--price=0}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate Monthly Plan Price';

  /**
   * EU countries
   */

  public $countriesEU = [];

  public function __construct(public SubscriptionManagerDR $manager, public DigitalRiverService $drService)
  {
    parent::__construct();

    $this->countriesEU = include __DIR__ . '/eu-country-rates.php';
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $subcmd = $this->argument('subcmd');
    if ($subcmd === 'fetch-rate') {
      $this->fetchTaxRates($this->countriesEU);
    } else if ($subcmd === 'update-plan') {
      $monthPrice = $this->option('price');
      if ($monthPrice <= 0) {
        $this->error('--price=0 is not valid');
        return self::FAILURE;
      }
      $this->updatePlans($monthPrice);
    } else {
      $this->printHelp();
    }

    return self::SUCCESS;
  }

  public function printHelp(): void
  {
    $this->info('plan:price {subcmd=eu-tax}');
    $this->info('subcmd:');
    $this->info('  fetch-rate   : fetch tax rate from DR');
    $this->info('  update-plan  : update monthly and annual plan prices');
    $this->info('');
    $this->info('options:');
    $this->info('  --price      : monthly plan price');
  }

  public function updatePlans($monthAmount): void
  {
    $annualAmount = floor($monthAmount * 12 * 0.9);

    $this->info('update monthly plan amount to ' . $monthAmount);
    $this->info('update annual  plan amount to ' . $annualAmount);

    /**
     * update monthly plan
     * 
     * @var Plan $monthPlan
     */
    $monthPlan = Plan::public()
      ->where('product_name', 'Leonardo™ Design Studio Pro')
      ->where('interval', Plan::INTERVAL_MONTH)
      ->where('interval_count', 1)
      ->where('subscription_level', 2)
      ->first();

    $priceList = $monthPlan->price_list;
    foreach ($priceList as &$price) {
      $country = $price['country'];
      if (isset($this->countriesEU[$country])) {
        $price['price'] = number_format($monthAmount / (1 + $this->countriesEU[$country]), 2);
      }
    }
    $this->info('monthly plan price list (old): ' . json_encode($monthPlan->price_list, JSON_PRETTY_PRINT));
    $this->info('monthly plan price list (new): ' . json_encode($priceList, JSON_PRETTY_PRINT));
    $monthPlan->price_list = $priceList;
    $monthPlan->save();

    /**
     * update annual plan
     * 
     * @var Plan $annualPlan
     */
    $annualPlan = Plan::public()
      ->where('product_name', 'Leonardo™ Design Studio Pro')
      ->where('interval', Plan::INTERVAL_YEAR)
      ->where('interval_count', 1)
      ->where('subscription_level', 2)
      ->first();

    $priceList = $annualPlan->price_list;
    foreach ($priceList as &$price) {
      $country = $price['country'];
      if (isset($this->countriesEU[$country])) {
        $price['price'] = number_format($monthAmount / (1 + $this->countriesEU[$country]), 2);
      }
    }
    $this->info('annual plan price list (old): ' . json_encode($annualPlan->price_list, JSON_PRETTY_PRINT));
    $this->info('annual plan price list (new): ' . json_encode($priceList, JSON_PRETTY_PRINT));
    $annualPlan->price_list = $priceList;
    $annualPlan->save();
  }

  /**
   * @param array $countries
   */
  public function fetchTaxRates($countries): void
  {
    $currency = 'EUR';

    /* default is Germany address */
    $address = [
      "line1" => "Hardenbergstraße 33",
      "line2" => "",
      "city" => "Berlin",
      "state" => "BE",
      "country" => "DE",
      "postcode" => "10623",
    ];

    $phpFile = "";

    $phpFile .= "<?php\n\n";
    $phpFile .= "return [\n";



    foreach ($countries as $country => $rate) {
      // tax retrieve checkout
      $checkoutRequest = new DrCheckoutRequest();
      $checkoutRequest->setCurrency($currency);
      $checkoutRequest->setEmail('user1.test@iifuture.com');
      $checkoutRequest->setBrowserIp(request()->ip());
      $checkoutRequest->setBillTo((new DrBilling())
        ->setEmail('user1.test@iifuture.com')
        ->setAddress((new DrAddress())
          ->setLine1($address['line1'])
          ->setLine2($address['line2'])
          ->setCity($address['city'])
          ->setPostalCode($address['postcode'])
          ->setState($address['state'])
          ->setCountry($country)));
      $checkoutRequest->setItems([
        (new DrSkuRequestItem())
          ->setProductDetails((new DrProductDetails())
            ->setSkuGroupId(config('dr.sku_grp_subscription'))
            ->setName('Tax Rate Precalculation'))
          ->setPrice(100)
      ]);
      $checkoutRequest->setTaxInclusive(false);
      $checkoutRequest->setUpstreamId(config('dr.tax_rate_pre_calcualte_id'));

      // retrieve tax rate
      $checkout = $this->drService->checkoutApi->createCheckouts($checkoutRequest);

      // remove checkout
      $this->drService->checkoutApi->deleteCheckouts($checkout->getId());

      $taxRate = $checkout->getItems()[0]->getTax()->getRate();
      $taxRateReal = $checkout->getTotalTax() == 0 ? 0 : $taxRate;

      $phpFile .= sprintf("  '%s' => %g,\n", $country, $taxRateReal);

      printf('.');
    }

    $phpFile .= "];\n";

    printf(" Done!\n");
    // printf($phpFile);

    file_put_contents(__DIR__ . '/eu-country-rates.php', $phpFile);
  }
}
