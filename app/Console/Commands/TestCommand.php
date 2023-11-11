<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use Illuminate\Console\Command;

use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\Address as DrAddress;
use DigitalRiver\ApiSdk\Model\Billing as DrBilling;
use DigitalRiver\ApiSdk\Model\SkuRequestItem as DrSkuRequestItem;
use DigitalRiver\ApiSdk\Model\SkuUpdateRequestItem as DrSkuUpdateRequestItem;
use DigitalRiver\ApiSdk\Model\CheckoutTaxIdentifierRequest as DrCheckoutTaxIdentifierRequest;
use DigitalRiver\ApiSdk\Model\ProductDetails as DrProductDetails;

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

  public function __construct(public SubscriptionManagerDR $manager, public DigitalRiverService $drService)
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
    // $this->retrieveTaxRate();
    $this->updateTax();

    return self::SUCCESS;
  }

  public function retrieveTaxRate(): void
  {
    /** @var User $user */
    $user = User::where('name', 'user1.test')->first();
    $billing_info = $user->billing_info;
    $billing_info->address = [
      "city" => "Las Cruces",
      "line1" => "2775 Roadrunner Parkway Apt 5102",
      "line2" => "",
      "state" => "NM",
      "country" => "US",
      "postcode" => "88011-8112",
    ];
    $billing_info->save();

    $this->manager->createOrUpdateCustomer($billing_info);

    printf(
      "%12s%12s%12s%12s%12s%12s\n",
      "taxRate",
      "subtotal",
      "totalTax",
      "totalAmount",
      "calcTax",
      "calcTaxFull",
    );

    $price = 10.00;
    while ($price < 11.00) {
      // tax retrieve checkout
      $checkoutRequest = new DrCheckoutRequest();
      $checkoutRequest->setCustomerId($user->getDrCustomerId());
      $checkoutRequest->setCurrency('USD');
      $checkoutRequest->setEmail($billing_info['email']);
      $checkoutRequest->setBrowserIp(request()->ip());
      $checkoutRequest->setBillTo((new DrBilling())
        ->setEmail($billing_info['email'])
        ->setAddress((new DrAddress())
          ->setLine1($billing_info['address']['line1'])
          ->setLine2($billing_info['address']['line2'])
          ->setCity($billing_info['address']['city'])
          ->setPostalCode($billing_info['address']['postcode'])
          ->setState($billing_info['address']['state'])
          ->setCountry($billing_info['address']['country'])));
      $checkoutRequest->setItems([
        (new DrSkuRequestItem())
          ->setProductDetails((new DrProductDetails())
            ->setSkuGroupId(config('dr.sku_grp_subscription'))
            ->setName('Tax Rate Precalculation'))
          ->setPrice($price)
      ]);
      $checkoutRequest->setTaxInclusive(false);
      $checkoutRequest->setCustomerType($billing_info['customer_type']);
      $checkoutRequest->setUpstreamId(config('dr.tax_rate_pre_calcualte_id'));

      // retrieve tax rate
      $checkout = $this->drService->checkoutApi->createCheckouts($checkoutRequest);

      // remove checkout (TODO: moved to after response?)
      $this->drService->checkoutApi->deleteCheckouts($checkout->getId());
      $taxRate = $checkout->getItems()[0]->getTax()->getRate();
      $subtotal = $checkout->getSubtotal();
      $totalTax = $checkout->getTotalTax();
      $totalAmount = $checkout->getTotalAmount();
      $calcTax = round($subtotal * $taxRate, 2);
      $calcTaxFull = round($subtotal * $taxRate, 4);

      if ($totalTax != $calcTax) {
        printf(
          "%12s%12s%12s%12s%12s%12s\n",
          $taxRate,
          $subtotal,
          $totalTax,
          $totalAmount,
          $calcTax,
          $calcTaxFull,
        );
      }
      $price += 0.01;
    }
  }


  public function updateTax()
  {
    LaunchSteps::fixTaxRateAndTaxAmount();
  }
}
