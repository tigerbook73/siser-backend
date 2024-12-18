<?php

namespace App\Console\Commands;

use App\Models\BillingInfo;
use App\Models\Coupon;
use App\Models\LicensePlan;
use App\Models\LicensePlanDetail;
use App\Models\Paddle\PriceCustomData;
use App\Models\Paddle\ProductCustomData;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\TaxId;
use App\Notifications\SubscriptionNotification;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\Paddle\AddressService;
use App\Services\Paddle\BusinessService;
use App\Services\Paddle\CustomerService;
use App\Services\Paddle\DiscountService;
use App\Services\Paddle\PaddleService;
use App\Services\Paddle\PriceService;
use App\Services\Paddle\ProductService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Paddle\SDK\Entities\Shared\Status;
use Paddle\SDK\Exceptions\ApiError;
use Paddle\SDK\Resources\Customers\Operations\UpdateCustomer;
use Paddle\SDK\Resources\Prices\Operations\ListPrices;

class PaddleCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'paddle:cmd {subcmd=help} {--force}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Execute paddle command.';


  public function __construct(
    public PaddleService $paddleService,
    public AddressService $addressService,
    public BusinessService $businessService,
    public CustomerService $customerService,
    public DiscountService $discountService,
    public PriceService $priceService,
    public ProductService $productService,
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
    $subcmd = $this->argument('subcmd');
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan paddle:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  sync-customer:   sync customers to paddle');
      $this->info('  sync-product:    sync products & plans to paddle');
      $this->info('  sync-discount:   sync discounts to paddle');
      $this->info('  sync-all:        sync all to paddle');
      $this->info('  archive-all:     archive all');
      $this->info('  send-stopped:    send emails to stopped customers');
      $this->info('  send-renew:      send emails to renewal customers');
      $this->info('  stop-all-dr:     stop all digital river subscriptions');
      $this->info('');

      return self::SUCCESS;
    }

    $force = $this->option('force');

    switch ($subcmd) {
      case 'update-email':
        $this->updateEmail();
        return self::SUCCESS;

      case 'sync-customer':
        $this->syncCustomers(force: $force);
        return self::SUCCESS;

      case 'sync-product':
        $this->syncProducts(force: $force);
        $this->syncPrices(force: $force);
        return self::SUCCESS;

      case 'sync-discount':
        $this->syncDiscounts(force: $force);
        return self::SUCCESS;

      case 'sync-all':
        $this->syncProducts(force: $force);
        $this->syncPrices(force: $force);
        $this->syncCustomers(force: $force);
        $this->syncDiscounts(force: $force);
        return self::SUCCESS;

      case 'archive-all':
        if (env('APP_TEST_CODE')) {
          $this->archiveAllCustomers();
        }
        return self::SUCCESS;

      case 'send-stopped':
        $this->sendEmailToStoppedCustomers();
        return self::SUCCESS;

      case 'send-renew':
        $this->sendEmailToRenewCustomers();
        return self::SUCCESS;

      case 'stop-all-dr':
        $this->stopAllDigitalRiver();
        return self::SUCCESS;

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  /**
   * sync billing info's email from user's email
   */
  public function updateEmail()
  {
    DB::statement('
      UPDATE billing_infos
      JOIN users ON billing_infos.user_id = users.id
      SET billing_infos.email = users.email
    ');

    $this->info('Billing info emails updated successfully.');
  }


  /**
   * this shall be a one time synchronization
   */
  public function syncCustomers(bool $force)
  {
    BillingInfo::whereNotNull('address->postcode')
      ->where('address->postcode', '!=', '')
      ->chunkById(60, function ($billingInfos) use ($force) {

        // API rate limit: 100 request per minutes
        $time = now();
        $apiCall = 0;

        /** @var BillingInfo $billingInfo */
        foreach ($billingInfos as $billingInfo) {
          try {
            if ($billingInfo->getMeta()->paddle->customer_id && !$force) {
              $this->info("Paddle customer for user \"{$billingInfo->email}\" already exists.");
              continue;
            }
            $paddleCustomer = $this->customerService->createOrUpdatePaddleCustomer($billingInfo);
            $this->info("Paddle customer \"{$paddleCustomer->email}\" created or updated.");
            $apiCall++;


            $paddleAddress = $this->addressService->createOrUpdatePaddleAddress($billingInfo);
            $this->info("Paddle address \"{$paddleAddress->countryCode->getValue()} {$paddleAddress->postalCode}\" for customer \"{$paddleCustomer->email}\" created or updated.");
            $apiCall++;

            if (
              $billingInfo->organization &&
              TaxId::where('user_id', $billingInfo->user_id)->count() > 0
            ) {
              $paddleBusiness = $this->businessService->createOrUpdatePaddleBusiness($billingInfo);
              $this->info("Paddle business \"{$paddleBusiness->name}\" for customer \"{$paddleCustomer->email}\" created or updated.");
              $apiCall++;
            }
          } catch (ApiError $e) {
            $this->warn("Failed to create/update paddle address/business for customer \"{$billingInfo->email}\".");
            $this->error("Message: {$e->getMessage()}, Field: " .
              ($e->fieldErrors[0]->field ?? '') .
              " : " .
              ($e->fieldErrors[0]->error ?? ''));
          }
        }

        $diff = $time->diffInSeconds(now());
        $allowance = $apiCall / 150 * 60;
        $sleep = ($allowance - $diff) > 0 ? $allowance - $diff : 0;
        $this->info("Sleeping for " . ($sleep + 1) . " seconds.");
        sleep($sleep + 1);
      });
  }


  public function syncProducts(bool $force)
  {
    /**
     * find local products
     * find paddle products
     *
     * if local product has paddle id, update paddle product
     * if local product has no paddle id, create paddle product and update local product
     */

    /**
     * sync products
     */

    /** @var Collection<int, Product> $products */
    $products = Product::whereIn('type', [
      Product::TYPE_SUBSCRIPTION,
      Product::TYPE_LICENSE_PACKAGE,
    ])->get();
    $paddleProducts = $this->paddleService->listProducts();

    foreach ($products as $product) {
      try {
        $paddleProduct = $paddleProducts->first(fn($paddleProduct) => $paddleProduct->id == $product->getMeta()->paddle->product_id);
        if (!$paddleProduct) {
          // try to rebuild relationship via paddleProduct.customData.product_name
          $paddleProduct = $paddleProducts->first(fn($paddleProduct) => ProductCustomData::from($paddleProduct->customData?->data)->product_name == $product->name);
          if ($paddleProduct) {
            $this->productService->updateProduct($product, $paddleProduct);
          }
        }

        // if $paddle product exists, update paddle product if required
        if ($paddleProduct) {
          if (
            $force ||
            $product->updated_at->gt(ProductCustomData::from($paddleProduct->customData?->data)->product_timestamp ?? "2000-01-01")
          ) {
            $paddleProduct = $this->productService->updatePaddleProduct($product);
            $this->info("Paddle product \"{$paddleProduct->name}\" updated.");
          } else {
            $this->info("Paddle product \"{$paddleProduct->name}\" is up-to-date.");
          }
          continue;
        } else {
          $paddleProduct = $this->productService->createPaddleProduct($product);
          $this->info("Paddle product \"{$paddleProduct->name}\" created.");
        }
      } catch (ApiError $e) {
        $this->warn("Failed to create/update paddle product for product \"{$product->name}\".");
        $this->error("Message: {$e->getMessage()}, Field: " .
          ($e->fieldErrors[0]->field ?? '') .
          " : " .
          ($e->fieldErrors[0]->error ?? ''));
      }
    }

    /**
     * archive paddle product that not exists in local products
     */
    $paddleProducts = $this->paddleService->listProducts();
    foreach ($paddleProducts as $paddleProduct) {
      // keep test products
      if ((ProductCustomData::from($paddleProduct->customData?->data)->product_name) == 'TEST') {
        continue;
      }

      $product = $products->first(fn($product) => $product->getMeta()->paddle->product_id == $paddleProduct->id);
      if (!$product) {
        $this->paddleService->archiveProduct($paddleProduct->id);
        $this->info("Paddle product \"{$paddleProduct->name}\" archived.");
      }
    }

    return self::SUCCESS;
  }

  /**
   * sync prices, must be called after syncProducts
   */
  public function syncPrices(bool $force)
  {
    $this->syncSubscriptionPrices($force);
    // $this->syncLicensePackagePrices($force); // TODO: ...
  }

  /**
   * sync subscription prices
   */
  public function syncSubscriptionPrices(bool $force)
  {
    $products = Product::whereIn('type', [
      Product::TYPE_SUBSCRIPTION,
    ])->get();

    foreach ($products as $product) {
      $this->syncSubscriptionPricesForProduct($product, $force);
    }
  }


  /**
   * sync subscription prices for a product
   */
  public function syncSubscriptionPricesForProduct(Product $product, bool $force)
  {
    $plans = $product->plans;
    $paddlePrices = $this->paddleService->listPrices(new ListPrices(
      productIds: [$product->getMeta()->paddle->product_id]
    ));

    foreach ($plans as $plan) {
      try {
        $paddlePrice = $paddlePrices->first(fn($paddlePrice) => $paddlePrice->id == $plan->getMeta()->paddle->price_id);
        if (!$paddlePrice) {
          // try to rebuild relationship via paddlePrice.customData.plan_id
          $paddlePrice = $paddlePrices->first(fn($paddlePrice) => PriceCustomData::from($paddlePrice->customData?->data)->plan_id == $plan->id);
          if ($paddlePrice) {
            $this->priceService->updatePlan($plan, $paddlePrice);
          }
        }

        // if $paddle price exists, update paddle price if required
        if ($paddlePrice) {
          if (
            $force ||
            $plan->updated_at->gt(PriceCustomData::from($paddlePrice->customData?->data)->plan_timestamp ?? "2000-01-01")
          ) {
            $paddlePrice = $this->priceService->updatePaddlePrice($plan);
            $this->info("Paddle price \"{$paddlePrice->name}\" updated.");
          } else {
            $this->info("Paddle price \"{$paddlePrice->name}\" is up-to-date.");
          }
        } else {
          if ($plan->status == Plan::STATUS_ACTIVE) {
            $paddlePrice = $this->priceService->createPaddlePrice($plan);
            $this->info("Paddle price \"{$paddlePrice->name}\" created.");
          } else {
            // skipped inactive plan;
          }
        }
      } catch (ApiError $e) {
        $this->warn("Failed to create/update paddle price for plan \"{$plan->name}\".");
        $this->error("Message: {$e->getMessage()}, Field: " .
          ($e->fieldErrors[0]->field ?? '') .
          " : " .
          ($e->fieldErrors[0]->error ?? ''));
      }
    }

    /**
     * archive paddle price that not exists in local products
     */
    $paddlePrices = $this->paddleService->listPrices(new ListPrices(
      productIds: [$product->getMeta()->paddle->product_id],
    ));
    foreach ($paddlePrices as $paddlePrice) {
      // keep test products
      if ((PriceCustomData::from($paddlePrice->customData?->data)->product_name) == 'TEST') {
        continue;
      }

      $plan = $plans->first(fn($plan) => $plan->getMeta()->paddle->price_id == $paddlePrice->id);
      if (!$plan) {
        $this->paddleService->archivePrice($paddlePrice->id);
        $this->info("Paddle price \"{$paddlePrice->name}\" archived.");
      }
    }

    return self::SUCCESS;
  }

  /**
   * sync license package prices, must be called after syncProducts
   */
  public function syncLicensePackagePrices(bool $force)
  {
    LicensePlan::createOrRefreshAll();

    /** @var Collection<int, LicensePlan> $licensePlans */
    $licensePlans = LicensePlan::get();

    $paddleSubscriptionPrices = $this->paddleService->listPrices(new ListPrices(
      productIds: Product::where('type', Product::TYPE_SUBSCRIPTION)->get()->map(
        fn($paddleSubscriptionPrice) => $paddleSubscriptionPrice->getMeta()->paddle->product_id
      )->all(),
    ));
    $paddleLicensePrices = $this->paddleService->listPrices(new ListPrices(
      productIds: Product::where('type', Product::TYPE_LICENSE_PACKAGE)->get()->map(
        fn($paddleLicensePrice) => $paddleLicensePrice->getMeta()->paddle->product_id
      )->all(),
    ));

    foreach ($licensePlans as $licensePlan) {
      $paddleSubscriptionPrice = $paddleSubscriptionPrices->first(
        fn($paddleSubscriptionPrice) => $paddleSubscriptionPrice->id == $licensePlan->plan->getMeta()->paddle->price_id
      );

      $details = $licensePlan->details;
      foreach ($details as $index => $detail) {
        try {
          $licensePlanDetail = $licensePlan->getDetail($index + 1);
          $paddlePrice = $paddleLicensePrices->first(
            fn($price) => ($price->id == $licensePlanDetail->paddle_price_id)
          );
          if (!$paddlePrice) {
            // try to rebuild relationship
            $paddlePrice = $paddleLicensePrices->first(
              fn($paddlePrice) => (
                $paddlePrice->billingCycle->interval == $licensePlan->interval &&
                $paddlePrice->billingCycle->frequency == $licensePlan->interval_count &&
                PriceCustomData::from($paddlePrice->customData?->data)->quantity == $licensePlanDetail->quantity)
            );
            if ($paddlePrice) {
              $this->priceService->updateLicensePlan($licensePlan, $paddlePrice, $licensePlanDetail->quantity);
            }
          }

          if ($paddlePrice) {
            if (
              $force ||
              $licensePlan->updated_at->gt(PriceCustomData::from($paddlePrice->customData?->data)->license_plan_timestamp ?? "2000-01-01")
            ) {
              $paddlePrice = $this->priceService->updatePaddleLicensePrice($paddleSubscriptionPrice, $licensePlan, $index + 1);
              $this->info("Paddle license price {$licensePlan->plan->name} \"{$paddlePrice->name}\" updated.");
            } else {
              $this->info("Paddle license price {$licensePlan->plan->name} \"{$paddlePrice->name}\" is up-to-date.");
            }
          } else {
            $paddlePrice = $this->priceService->createPaddleLicensePrice($paddleSubscriptionPrice, $licensePlan, $index + 1);
            $this->info("Paddle license price {$licensePlan->plan->name} \"{$paddlePrice->name}\" created.");
          }
        } catch (ApiError $e) {
          $this->warn("Failed to create/update paddle license price for plan \"{$licensePlan->plan->name}\".");
          $this->error("Message: {$e->getMessage()}, Field: " .
            ($e->fieldErrors[0]->field ?? '') .
            " : " .
            ($e->fieldErrors[0]->error ?? ''));
        }
      }
    }
  }


  public function syncDiscounts(bool $force)
  {
    Coupon::chunkById(60, function ($coupons) use ($force) {

      // API rate limit: 100 request per minutes
      $time = now();
      $apiCall = 0;

      /** @var Coupon $coupon */
      foreach ($coupons as $coupon) {
        try {
          if ($coupon->getMeta()->paddle->discount_id) {
            if (
              $force ||
              $coupon->updated_at->subSeconds(3)->gt($coupon->getMeta()->paddle->paddle_timestamp ?? "2000-01-01")
            ) {
              $paddleDiscount = $this->discountService->updatePaddleDiscount($coupon);
              $this->info("Paddle coupon \"{$coupon->name}\" for event \"{$coupon->coupon_event}\" updated");
              $apiCall++;
            } else {
              $this->info("Paddle coupon \"{$coupon->name}\" for event \"{$coupon->coupon_event}\" is up-to-date.");
            }
          } else {
            if (
              $coupon->status == Coupon::STATUS_ACTIVE &&
              $coupon->end_date->gt(now())
            ) {
              $paddleDiscount = $this->discountService->createPaddleDiscount($coupon);
              $this->info("Paddle coupon \"{$coupon->name}\" for event \"{$coupon->coupon_event}\" created");
              $apiCall++;
              continue;
            } else {
              // skip inactive coupon
              $this->info("Paddle coupon \"{$coupon->name}\" for event \"{$coupon->coupon_event}\" skipped");
            }
          }
        } catch (ApiError $e) {
          $this->warn("Failed to create/update paddle discount for coupon \"{$coupon->name}\".");
          $this->error("Message: {$e->getMessage()}, Field: " .
            ($e->fieldErrors[0]->field ?? '') .
            " : " .
            ($e->fieldErrors[0]->error ?? ''));
        }
      }

      $diff = $time->diffInSeconds(now());
      $allowance = $apiCall / 150 * 60;
      $sleep = ($allowance - $diff) > 0 ? $allowance - $diff : 0;
      $this->info("Sleeping for " . ($sleep + 1) . " seconds.");
      sleep($sleep + 1);
    });
  }

  public function archiveAllCustomers()
  {
    $this->info("Starting to archive all customers...");
    foreach ($this->paddleService->paddle->customers->list() as $paddleCustomers) {
      $this->paddleService->paddle->customers->update($paddleCustomers->id, new UpdateCustomer(
        status: Status::Archived(),
      ));
      printf(".");
    }
    $this->info("All customers archived.");
  }

  public function sendEmailToStoppedCustomers()
  {
    $this->info("Starting to send email to stopped customers...");

    Subscription::whereNotNull('dr_subscription_id')
      ->where('status', Subscription::STATUS_FAILED)
      ->where('end_date', '>', '2024-10-01')
      ->whereIn('payment_method_info->type', ['payPalBilling', 'googlePay'])
      ->whereNull('dr->email')
      ->chunkById(10, function ($subscriptions) {
        /** @var Collection<int, Subscription> $subscriptions */
        foreach ($subscriptions as $subscription) {
          // skip active users
          if ($subscription->user->subscription_level == 2) {
            continue;
          }
          $subscription->sendNotification(SubscriptionNotification::NOTIF_WELCOME_BACK_FOR_STOPPED);
          $dr = $subscription->dr;
          $dr['email'] = 'welcome-back';
          $subscription->dr = $dr;
          $subscription->save();
          printf(".");
        }
        printf("\n");
        $this->info("Sent welcome back email to {$subscriptions->count()} subscriptions, sleeping for 3 second...");
        sleep(3);
      });

    $this->info("All emails are queued.");
  }

  public function stopAllDigitalRiver()
  {
    $this->info("Starting to stop all digital river subscriptions...");

    $drService = new DigitalRiverService();
    Subscription::whereNotNull('dr_subscription_id')
      ->whereNotNull('dr->subscription_id')
      ->whereNull('dr->stopped')
      ->where('status', Subscription::STATUS_ACTIVE)
      ->whereNot('sub_status', Subscription::SUB_STATUS_CANCELLING)
      ->chunkById(10, function ($subscriptions) use ($drService) {
        /** @var Collection<int, Subscription> $subscriptions */
        foreach ($subscriptions as $subscription) {
          // skip if env is local
          if (env('APP_ENV') !== 'local') {
            try {
              $drService->cancelSubscription($subscription->dr_subscription_id);
            } catch (\Exception $e) {
              $this->error("Failed to cancel subscription {$subscription->dr_subscription_id}.");
              continue;
            }
          }
          $dr = $subscription->dr;
          $dr['stopped'] = 'by-force';
          $subscription->dr = $dr;
          $subscription->save();

          printf(".");
        }
        printf("\n");
        $this->info("Cancel {$subscriptions->count()} subscriptions, sleeping for 1 second...");
        sleep(1);
      });

    $this->info("All subscriptions are stopped.");
  }

  public function sendEmailToRenewCustomers()
  {
    return self::SUCCESS;
  }
}
