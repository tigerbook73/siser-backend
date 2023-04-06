<?php

namespace App\Console\Commands;

use App\Models\GeneralConfiguration;
use App\Services\DigitalRiver\DigitalRiverService;
use DigitalRiver\ApiSdk\Model\Checkout;
use DigitalRiver\ApiSdk\Model\Customer;
use DigitalRiver\ApiSdk\Model\Order;
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
    if (!config('dr.dr_unit_test', false)) {
      $this->warn('This command can only be executed under test mode');
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
        $this->error("Invalid subcmd: ${subcmd}");
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
    $this->info("Default Plan:");
    $this->info((string)$defaultPlan);
    $this->info("Create or update default plan ... done!");

    return 0;
  }

  public function enableWebhook(bool $enable)
  {
    $drService = new DigitalRiverService();

    // create / update hook
    $this->info('Update default webhooks ...');
    $drService->updateDefaultWebhook($enable);
    $this->info('Update default webhooks ... done');
  }

  public function clear()
  {
    $drService = new DigitalRiverService();

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

  function ignore($callback)
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
