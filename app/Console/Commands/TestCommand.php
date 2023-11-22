<?php

namespace App\Console\Commands;

use App\Models\BillingInfo;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use Illuminate\Console\Command;

use DigitalRiver\ApiSdk\Model\CheckoutRequest as DrCheckoutRequest;
use DigitalRiver\ApiSdk\Model\Address as DrAddress;
use DigitalRiver\ApiSdk\Model\Billing as DrBilling;
use DigitalRiver\ApiSdk\Model\SkuRequestItem as DrSkuRequestItem;
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
    $this->fixSubscriptionData();

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

  public function fixSubscriptionData()
  {
    /**
     * update basic plan's interval
     */
    $count = 0;
    printf("Updating basic plan's interval ...\n");
    $count = Plan::where('id', config('siser.plan.default_machine_plan'))->where('interval', '<>', Plan::INTERVAL_LONGTERM)
      ->update(['interval' => Plan::INTERVAL_LONGTERM]);
    printf("Updating basic plan's interval ... %d\n", $count);


    /**
     * create default billing info if not exist
     */
    $count = 0;
    printf("Creating default billing info ...\n");
    User::has('billing_info', '<=', 0)
      ->has('subscriptions')
      ->chunkById(100, function ($users) use (&$count) {
        foreach ($users as $user) {
          BillingInfo::createDefault($user);
          $count++;
        }
      });
    printf("Creating default billing info ... %d\n", $count);

    /**
     * update subscription's billint_info
     */
    $count = 0;
    printf("Updating subscription's billing info ...\n");
    Subscription::whereNull('billing_info')
      ->chunkById(100, function ($subscriptions) use (&$count) {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
          $subscription->billing_info = $subscription->user->billing_info->info();
          $subscription->save();
          $count++;
        }
      });
    printf("Updating subscription's billing info ... %d\n", $count);

    /**
     * fix subscription->plan_info->interval, interval_count
     */
    $count = 0;
    printf("Fixing monthly subscription's interval & count ...\n");
    $count = Subscription::where('plan_info->name', 'Leonardo™ Design Studio Pro Monthly Plan')
      ->whereNull('plan_info->interval')
      ->update(
        [
          'plan_info->interval' => 'month',
          'plan_info->interval_count' => 1
        ]
      );
    printf("Fixing monthly subscription's interval & count ... %d\n", $count);

    /**
     * fix subscription->plan_info->interval, interval_count
     */
    $count = 0;
    printf("Fixing annual subscription's plan info's interval & count ...\n");
    $count = Subscription::where('plan_info->name', 'Leonardo™ Design Studio Pro Annual Plan')
      ->whereNull('plan_info->interval')
      ->update(
        [
          'plan_info->interval' => 'year',
          'plan_info->interval_count' => 1
        ]
      );
    printf("Fixing annual subscription's plan info's interval & count ... %d\n", $count);


    /**
     * fix subscription basic plans currency (shall not always be USD)
     */
    $count = 0;
    printf("Fixing basic subscription's currency ...\n");
    /** @var Plan $basicPlan */
    $basicPlan = Plan::find(config('siser.plan.default_machine_plan'));
    Subscription::where('plan_id', 1)
      ->where('subscription_level', 1)
      ->where('plan_info->name', 'Leonardo™ Design Studio Basic Plan (free)')
      ->chunkById(100, function ($subscriptions) use ($basicPlan, &$count) {
        /** @var Subscription $subscription */
        foreach ($subscriptions as $subscription) {
          $plan_info = $basicPlan->info($subscription->billing_info['address']['country']);
          $subscription->plan_info = $plan_info;
          $subscription->currency = $plan_info['price']['currency'];
          $subscription->save();
          $count++;
        }
      });
    printf("Fixing basic subscription's currency ... %d\n", $count);
  }
}
