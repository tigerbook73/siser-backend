<?php

namespace App\Console\Commands;

use App\Models\GeneralConfiguration;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use DigitalRiver\ApiSdk\Model\Checkout;
use DigitalRiver\ApiSdk\Model\Customer;
use DigitalRiver\ApiSdk\Model\Order;
use DigitalRiver\ApiSdk\Model\Plan;
use DigitalRiver\ApiSdk\Model\Subscription;
use Illuminate\Console\Command;

class DrCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'dr:cmd {subcmd=help}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'initialize prebuild data in DigitalRiver.';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    if (config('dr.dr_mode') == 'prod') {
      $this->warn('This command can not be executed under "prod" mode');
      return -1;
    }

    $subcmd = $this->argument('subcmd');
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan dr:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  init:            initialize prebuild data in DigitalRiver');
      $this->info('  clear:           try to clear all test data');
      $this->info('  enable-hook:     enable webhook');
      $this->info('  disable-hook:    disable webhook');
      return 0;
    }

    switch ($subcmd) {
      case 'init':
        return $this->init();

      case 'clear':
        return $this->clear();

      case 'enable-hook':
        return $this->enableWebhook(true);

      case 'disable-hook':
        return $this->enableWebhook(false);

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return -1;
    }
  }

  public function init()
  {
    $drService = new DigitalRiverService();

    // create / update default plan
    $this->info("Create or update default plan ...");
    try {
      $defaultPlan = $drService->getDefaultPlan();
      $defaultPlan = $drService->updateDefaultPlan(GeneralConfiguration::getConfiguration());
    } catch (\Throwable $th) {
      $defaultPlan = $drService->createDefaultPlan(GeneralConfiguration::getConfiguration());
    }
    $this->info("Default Plan: {$defaultPlan->getId()}");
    $this->info("Create or update default plan ... done!");

    return 0;
  }

  public function enableWebhook(bool $enable)
  {
    $drService = new SubscriptionManagerDR();

    // create / update hook
    $this->info('Update default webhooks ...');
    $drService->updateDefaultWebhook($enable);
    $this->info('Update default webhooks ... done');
  }

  public function clear()
  {
    $drService = new DigitalRiverService();

    /**
     * clear plans
     */
    $this->info('Clear plans ...');

    /** @var Plan[] $plans */
    $plans = $drService->planApi->listPlans(state: 'draft')->getData();
    foreach ($plans as $plan) {
      $this->info("  delete plan " . $plan->getId());
      $this->ignore(
        [$drService->planApi, 'deletePlans'],
        $plan->getId()
      );
    }

    $this->info('Clear plans ...');
    $this->info('');

    /**
     * clear subscriptions
     */

    $this->info('Clear subscriptions ...');

    /** @var Subscription[] $subscriptions */
    $subscriptions = $drService->subscriptionApi->listSubscriptions(state: 'draft')->getData();
    foreach ($subscriptions as $subscription) {
      $this->info("  delete subscription " . $subscription->getId());
      $this->ignore(
        [$drService->subscriptionApi, 'deleteSubscriptions'],
        $subscription->getId()
      );
    }
    $subscriptions = $drService->subscriptionApi->listSubscriptions(state: 'active')->getData();
    foreach ($subscriptions as $subscription) {
      $this->info("  cancel subscription " . $subscription->getId());
      $this->ignore(
        [$drService->subscriptionApi, 'updateSubscriptions'],
        $subscription->getId(),
        new Subscription(['state' => 'cancelled'])
      );
    }

    $this->info('Clear subscriptions ...');
    $this->info('');


    /**
     * clear checkout
     */

    $this->info('Clear checkouts ...');

    // /** @var Checkout[] $checkouts */
    // $checkouts = $drService->checkoutApi->listCheckouts()->getData();
    // foreach ($checkouts as $checkout) {
    //   $this->ignore(
    //     [$drService->checkoutApi, 'deleteCheckouts'],
    //     $checkout->getId()
    //   );
    // }

    $this->info('Clear checkouts ...');
    $this->info('');

    /**
     * clear order
     */

    $this->info('Clear orders ...');

    /** @var Order[] $orders */
    $orders = $drService->orderApi->listOrders(state: 'accepted')->getData();
    foreach ($orders as $order) {
      $this->info("  cancel order " . $order->getId());
      $drService->fulfillOrder(orderId: $order->getId(), cancel: true);
    }

    $this->info('Clear orders ...');
    $this->info('');

    /**
     * clear customers
     */

    $this->info('Clear customers ...');

    /** @var Customer[] $customers */
    $customers = $drService->customerApi->listCustomers()->getData();
    foreach ($customers as $customer) {
      foreach ($customer->getSources() ?? [] as $source) {
        $this->info("  delete customer source " . $source->getId());
        $this->ignore(
          [$drService->customerApi, 'deleteCustomerSource'],
          $customer->getId(),
          $source->getId()
        );
      };
      $this->info("  delete customer " . $customer->getId());
      $this->ignore(
        [$drService->customerApi, 'deleteCustomers'],
        $customer->getId()
      );
    }

    $this->info('Clear customers ...');
    $this->info('');

    return 0;
  }

  public function ignore($callback)
  {
    try {
      $args = func_get_args();
      array_shift($args);

      // sleep 1/20 seconds to prevent to exceed limits
      usleep(1000000 / 20);
      return call_user_func_array($callback, $args);
    } catch (\Throwable $th) {
      $this->info($th->getMessage());
      return null;
    }
  }
}
