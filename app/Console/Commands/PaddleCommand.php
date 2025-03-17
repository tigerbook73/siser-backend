<?php

namespace App\Console\Commands;

use App\Models\BillingInfo;
use App\Models\Coupon;
use App\Models\Invoice;
use App\Models\LicensePackage;
use App\Models\Paddle\PriceCustomData;
use App\Models\Paddle\ProductCustomData;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductInterval;
use App\Models\Refund;
use App\Models\Subscription;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Paddle\SDK\Entities\Discount\DiscountStatus;
use Paddle\SDK\Entities\Shared\Status;
use Paddle\SDK\Entities\Subscription\SubscriptionEffectiveFrom;
use Paddle\SDK\Entities\Subscription\SubscriptionStatus;
use Paddle\SDK\Exceptions\ApiError;
use Paddle\SDK\Resources\Customers\Operations\UpdateCustomer;
use Paddle\SDK\Resources\Discounts\Operations\UpdateDiscount;
use Paddle\SDK\Resources\Prices\Operations\ListPrices;
use Paddle\SDK\Resources\Prices\Operations\UpdatePrice;
use Paddle\SDK\Resources\Products\Operations\UpdateProduct;
use Paddle\SDK\Resources\Subscriptions\Operations\CancelSubscription;
use Paddle\SDK\Resources\Subscriptions\Operations\ListSubscriptions;

class PaddleCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'paddle:cmd
                          {subcmd=help : subcommand}
                          {--subscription= : subscription id}
                          {--invoice= : invoice id}
                          {--refund= : refund id}
                          {--notification= : notification id}
                          {--force}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Execute paddle command.';


  public function __construct(
    public SubscriptionManagerPaddle $manager
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
      $this->info('Usage: php artisan paddle:cmd {subcmd} {option} {--force}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:                display this information');
      $this->info('  sync-customer:       sync customers to paddle');
      $this->info('  sync-product:        sync products & plans to paddle');
      $this->info('  sync-price:          sync prices to paddle');
      $this->info('  sync-discount:       sync discounts to paddle');
      $this->info('  sync-all:            sync all to paddle');
      $this->info('  archive-all:         archive all');
      $this->info('  enable-hook:         enable & update webbook');
      $this->info('  refresh-model:       update model from paddle');
      $this->info('  replay-notification: replay notification');
      $this->info('');
      $this->info('options:');
      $this->info('  --subscription:      subscription id,  only for refresh-model subcmd');
      $this->info('  --invoice:           invoice id,       only for refresh-model subcmd');
      $this->info('  --refund:            refund id,        only for refresh-model subcmd');
      $this->info('  --notification:      notification id,  only for replay-notification subcmd');
      $this->info('  --force:             force update when running sync-* subcmds');
      $this->info('');

      return self::SUCCESS;
    }

    $force = $this->option('force');

    switch ($subcmd) {
      case 'sync-customer':
        $this->syncCustomers(force: $force);
        return self::SUCCESS;

      case 'sync-product':
        $this->syncProducts(force: $force);
        return self::SUCCESS;

      case 'sync-price':
        $this->syncPrices(force: $force);
        return self::SUCCESS;

      case 'sync-discount':
        $this->syncDiscounts(force: $force);
        return self::SUCCESS;

      case 'sync-all':
        $this->syncCustomers(force: $force);
        $this->syncProducts(force: $force);
        $this->syncPrices(force: $force);
        $this->syncDiscounts(force: $force);
        return self::SUCCESS;

      case 'archive-all':
        if (env('APP_TEST_CODE')) {
          $this->archiveAllCustomers();
          $this->archiveAllProducts();
          $this->archiveAllPrices();
          $this->archiveAllCoupon();
          $this->stopAllSubscription();
        }
        return self::SUCCESS;

      case 'refresh-model':
        $this->refreshModel();
        return self::SUCCESS;

      case 'enable-hook':
        $this->manager->updateDefaultWebhook(true);
        return self::SUCCESS;

      case 'replay-notification':
        $this->replayNotification();
        return self::SUCCESS;

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
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
            $paddleCustomer = $this->manager->customerService->createOrUpdatePaddleCustomer($billingInfo);
            $this->info("Paddle customer \"{$paddleCustomer->email}\" created or updated.");
            $apiCall++;

            $paddleAddress = $this->manager->addressService->createOrUpdatePaddleAddress($billingInfo);
            $this->info("Paddle address \"{$paddleAddress->countryCode->getValue()} {$paddleAddress->postalCode}\" for customer \"{$paddleCustomer->email}\" created or updated.");
            $apiCall++;
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
        if ($sleep > 0) {
          $this->info("Sleeping for " . ($sleep + 1) . " seconds.");
          sleep($sleep + 1);
        }
      });
  }


  public function syncProducts(bool $force)
  {
    /**
     * find products
     * find paddle products
     *
     * if product has paddle id, update paddle product
     * if product has no paddle id, create paddle product and update local product
     */

    $products = Product::listProducts();
    $paddleProducts = $this->manager->paddleService->listProducts();

    foreach ($products as $product) {
      try {
        $matchedPaddleProducts = $paddleProducts->filter(
          fn($paddleProduct) => (
            collect(ProductInterval::cases())
            ->some(
              fn($interval) => $product->getMeta()->paddle->getProductId($interval) === $paddleProduct->id
            )
          )
        );

        // if $paddle product exists, update paddle product if required
        foreach (ProductInterval::cases() as $interval) {
          $paddleProduct = $matchedPaddleProducts->first(
            fn($paddleProduct) => $product->getMeta()->paddle->getProductId($interval) === $paddleProduct->id
          );
          if ($paddleProduct) {
            if (
              $force ||
              (Carbon::parse($product->updated_at)
                ->subMinute()
                ->gt(ProductCustomData::from($paddleProduct->customData?->data)->product_timestamp ?? "2000-01-01"))
            ) {
              $paddleProduct = $this->manager->productService->updatePaddleProduct(
                $product,
                $interval
              );
              $this->info("Paddle product \"{$paddleProduct->name}/{$paddleProduct->id}\" updated.");
            } else {
              $this->info("Paddle product \"{$paddleProduct->name}/{$paddleProduct->id}\" is up-to-date.");
            }
            continue;
          } else {
            $paddleProduct = $this->manager->productService->createPaddleProduct($product, $interval);
            $this->info("Paddle product \"{$paddleProduct->name}/{$paddleProduct->id}\" created.");
          }
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
     * archive paddle product that not referenced by products
     */
    $paddleProducts = $this->manager->paddleService->listProducts();
    foreach ($paddleProducts as $paddleProduct) {
      // referenced by product
      if ($products->some(
        fn($product) => collect(ProductInterval::cases())->some(
          fn($interval) => $product->getMeta()->paddle->getProductId($interval) === $paddleProduct->id
        )
      )) {
        continue;
      };

      $this->manager->paddleService->archiveProduct($paddleProduct->id);
      $this->info("Paddle product \"{$paddleProduct->name}/{$paddleProduct->id}\" archived.");
    }

    return self::SUCCESS;
  }

  /**
   * sync prices, must be called after syncProducts
   */
  public function syncPrices(bool $force)
  {
    $this->syncSubscriptionPrices($force);
  }

  /**
   * sync subscription prices
   */
  public function syncSubscriptionPrices(bool $force)
  {
    $products = Product::listProducts();
    foreach ($products as $product) {
      $this->syncSubscriptionPricesForProduct($product, $force);
    }
  }


  /**
   * sync subscription prices for a product
   */
  public function syncSubscriptionPricesForProduct(Product $product, bool $force)
  {
    /** @var Collection<int,Plan> $plans */
    $plans = $product->plans()->public()->get();
    $productIds = collect(ProductInterval::cases())
      ->map(fn($interval) => $product->getMeta()->paddle->getProductId($interval))
      ->filter()
      ->all();
    $paddlePrices = $this->manager->paddleService->listPrices(new ListPrices(
      productIds: $productIds,
    ));

    foreach ($plans as $plan) {
      $planMeta  = $plan->getMeta();

      /* synchronize plan's standard price */
      try {
        $paddlePrice = $paddlePrices->first(fn($paddlePrice) => $planMeta->paddle->price_id === $paddlePrice->id);
        if ($paddlePrice) {
          if (
            $force ||
            (Carbon::parse($plan->updated_at)
              ->subMinute()
              ->gt(PriceCustomData::from($paddlePrice->customData?->data)->plan_timestamp ?? "2000-01-01"))
          ) {
            $paddlePrice = $this->manager->priceService->updatePaddlePrice($plan);
            $this->info("Paddle price \"{$paddlePrice->name}/{$paddlePrice->id}\" updated.");
          } else {
            $this->info("Paddle price \"{$paddlePrice->name}/{$paddlePrice->id}\" is up-to-date.");
          }
        } else {
          $paddlePrice = $this->manager->priceService->createPaddlePrice($plan);
          $this->info("Paddle price \"{$paddlePrice->name}/{$paddlePrice->id}\" created.");
        }
      } catch (ApiError $e) {
        $this->warn("Failed to create/update paddle price for plan \"{$plan->name}\".");
        $this->error("Message: {$e->getMessage()}, Field: " .
          ($e->fieldErrors[0]->field ?? '') .
          " : " .
          ($e->fieldErrors[0]->error ?? ''));
      }

      /* synchronize plan's license prices */
      $licensePackage = LicensePackage::findStandard();
      try {
        $currentQuantities = $planMeta->paddle->license_prices->getQuantities();
        $newQuantities = array_map(
          fn($priceRate) => $priceRate->quantity,
          $licensePackage?->getPriceTable()->price_list ?? []
        );
        $toRemoveQuantities = array_diff($currentQuantities, $newQuantities);

        // archive removed license quantities
        foreach ($toRemoveQuantities as $quantity) {
          $this->info("Paddle license price for quantity \"{$plan->name}/{$quantity}/{$plan->getMetaPaddleLicensePriceId($quantity)}\" removed.");
          $this->manager->priceService->removePaddleLicensePrices($plan, $quantity);
        }

        // update or create paddle license prices for new quantities
        foreach ($newQuantities as $quantity) {
          $paddleLicensePrice = $paddlePrices->first(fn($paddlePrice) => $planMeta->paddle->license_prices->getPriceId($quantity) === $paddlePrice->id);
          if ($paddleLicensePrice) {
            if (
              $force ||
              (Carbon::parse(max($plan->updated_at, $licensePackage->updated_at))
                ->subMinute()
                ->gt(PriceCustomData::from($paddleLicensePrice->customData?->data)->license_package_timestamp ?? "2000-01-01"))
            ) {
              $paddleLicensePrice = $this->manager->priceService->updatePaddleLicensePrice($plan, $licensePackage, $quantity);
              $this->info("Paddle license price for quantity \"{$plan->name}/{$quantity}/{$paddleLicensePrice->id}\" updated.");
            } else {
              $this->info("Paddle license price for quantity \"{$plan->name}/{$quantity}/{$paddleLicensePrice->id}\" is up-to-date.");
            }
          } else {
            $paddleLicensePrice = $this->manager->priceService->createPaddleLicensePrice($plan, $licensePackage, $quantity);
            $this->info("Paddle license price for quantity \"{$plan->name}/{$quantity}/{$paddleLicensePrice->id}\" created.");
          }
        }
      } catch (ApiError $e) {
        $this->warn("Failed to create/update paddle license price for plan \"{$plan->name}\".");
        $this->error("Message: {$e->getMessage()}, Field: " .
          ($e->fieldErrors[0]->field ?? '') .
          " : " .
          ($e->fieldErrors[0]->error ?? ''));
      }
    }

    /**
     * archive paddle price that not exists in local products
     */
    $paddlePrices = $this->manager->paddleService->listPrices(new ListPrices(productIds: $productIds));
    foreach ($paddlePrices as $paddlePrice) {
      $plan = $plans->first(
        fn($plan) => (
          $plan->getMeta()->paddle->price_id === $paddlePrice->id ||
          ($plan->getMeta()->paddle->license_prices->getPriceId(PriceCustomData::from($paddlePrice->customData?->data)->license_quantity) === $paddlePrice->id))
      );
      if (!$plan) {
        $this->manager->paddleService->archivePrice($paddlePrice->id);
        $this->info("Paddle price \"{$paddlePrice->name}/{$paddlePrice->id}\" archived.");
      }
    }

    return self::SUCCESS;
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
              (Carbon::parse($coupon->updated_at)
                ->subMinute()
                ->gt($coupon->getMeta()->paddle->paddle_timestamp ?? "2000-01-01"))
            ) {
              $paddleDiscount = $this->manager->discountService->updatePaddleDiscount($coupon);
              $this->info("Paddle coupon \"{$coupon->name}/{$paddleDiscount->id}\" for event \"{$coupon->coupon_event}\" updated");
              $apiCall++;
            } else {
              $this->info("Paddle coupon \"{$coupon->name}/{$coupon->getMeta()->paddle->discount_id}\" for event \"{$coupon->coupon_event}\" is up-to-date.");
            }
          } else {
            if (
              $coupon->status == Coupon::STATUS_ACTIVE &&
              $coupon->end_date->gt(now())
            ) {
              $paddleDiscount = $this->manager->discountService->createPaddleDiscount($coupon);
              $this->info("Paddle coupon \"{$coupon->name}/{$paddleDiscount->id}\" for event \"{$coupon->coupon_event}\" created or updated.");
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
      if ($sleep > 0) {
        $this->info("Sleeping for " . ($sleep + 1) . " seconds.");
        sleep($sleep + 1);
      }
    });

    // archive paddle discount that not exists in local coupons
    $this->archiveOrphanedDiscount();
  }

  /**
   * archive paddle discount that not exists in local coupons
   */
  public function archiveOrphanedDiscount()
  {
    // archive paddle discount that not exists in local coupons
    foreach ($this->manager->paddleService->paddle->discounts->list() as $paddleDiscount) {
      if (! Coupon::where('meta->paddle->discount_id', $paddleDiscount->id)->exists()) {
        $startTime = microtime(true);
        $this->manager->paddleService->archiveDiscount($paddleDiscount->id);
        $endTime = microtime(true);

        // if larger than 1/4 seconds, sleep for 1/4 - (end - start) seconds
        if ($endTime - $startTime < (1 / 4)) {
          usleep((int)(((1 / 4) - ($endTime - $startTime)) * 1000000));
        }

        $this->info("Paddle coupon \"{$paddleDiscount->code}/{$paddleDiscount->id}\" archived.");
      }
    }
  }

  public function archiveAllCustomers()
  {
    $this->info("");
    $this->info("Starting to archive all customers...");
    $count = 0;
    foreach ($this->manager->paddleService->paddle->customers->list() as $paddleCustomers) {
      $this->manager->paddleService->paddle->customers->update($paddleCustomers->id, new UpdateCustomer(
        status: Status::Archived(),
      ));
      printf(".");
      $count++;
    }
    if ($count !== 0) {
      printf("\n");
    }
    $this->info("All customers archived.");
  }

  public function archiveAllProducts()
  {
    $this->info("");
    $this->info("Starting to archive all products...");
    $count = 0;
    foreach ($this->manager->paddleService->paddle->products->list() as $paddleProducts) {
      $this->manager->paddleService->paddle->products->update($paddleProducts->id, new UpdateProduct(
        status: Status::Archived(),
      ));
      printf(".");
      $count++;
    }
    if ($count !== 0) {
      printf("\n");
    }
    $this->info("All products archived.");
  }

  public function archiveAllPrices()
  {
    $this->info("");
    $this->info("Starting to archive all prices...");
    $count = 0;
    foreach ($this->manager->paddleService->paddle->prices->list() as $paddlePrices) {
      $this->manager->paddleService->paddle->prices->update($paddlePrices->id, new UpdatePrice(
        status: Status::Archived(),
      ));
      printf(".");
      $count++;
    }
    if ($count !== 0) {
      printf("\n");
    }
    $this->info("All prices archived.");
  }

  public function archiveAllCoupon()
  {
    $this->info("");
    $this->info("Starting to archive all coupons...");
    $count = 0;
    foreach ($this->manager->paddleService->paddle->discounts->list() as $paddleDiscounts) {
      $this->manager->paddleService->paddle->discounts->update($paddleDiscounts->id, new UpdateDiscount(
        status: DiscountStatus::Archived(),
      ));
      printf(".");
      $count++;
    }
    if ($count !== 0) {
      printf("\n");
    }
    $this->info("All coupons archived.");
  }

  public function stopAllSubscription()
  {
    $this->info("");
    $this->info("Starting to stop all subscriptions...");
    $count = 0;
    foreach (
      $this->manager->paddleService->paddle->subscriptions->list(
        new ListSubscriptions(statuses: [SubscriptionStatus::Active()])
      ) as $paddleSubscriptions
    ) {
      $this->manager->paddleService->paddle->subscriptions->cancel($paddleSubscriptions->id, new CancelSubscription(
        SubscriptionEffectiveFrom::Immediately()
      ));
      printf(".");
      $count++;
    }
    if ($count !== 0) {
      printf("\n");
    }
    $this->info("All subscriptions stopped.");
  }

  public function refreshModel()
  {
    $subscriptionId = $this->option('subscription');
    $invoiceId = $this->option('invoice');
    $refundId = $this->option('refund');

    // there must be one and only one option in [subscription, invoice, refund] is set
    if ((int)empty($subscriptionId) + (int)empty($invoiceId) + (int)empty($refundId) != 2) {
      $this->error("One and only one option in [subscription, invoice, refund] is required.");
      return self::FAILURE;
    }

    if ($subscriptionId) {
      $subscription = Subscription::findById($subscriptionId);
      if (!$subscription) {
        $this->error("Subscription not found.");
        return self::FAILURE;
      }
      $this->manager->subscriptionService->refreshSubscription($subscription);
    } else if ($invoiceId) {
      $invoice = Invoice::findById($invoiceId);
      if (!$invoice) {
        $this->error("Invoice not found.");
        return self::FAILURE;
      }
      $this->manager->transactionService->refreshInvoice($invoice);
    } else if ($refundId) {
      $refund = Refund::findById($refundId);
      if (!$refund) {
        $this->error("Refund not found.");
        return self::FAILURE;
      }
      $this->manager->adjustmentService->refreshRefund($refund);
    }
    return self::SUCCESS;
  }

  public function replayNotification()
  {
    $notificationId = $this->option('notification');
    if (!$notificationId) {
      $this->error("Notification id is required.");
      return self::FAILURE;
    }
    $string = $this->manager->triggerEvent($notificationId, true);
    $this->info($string);
  }
}
