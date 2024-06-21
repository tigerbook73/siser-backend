<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManager;
use DigitalRiver\ApiSdk\Model\Customer as DrCustomer;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use DigitalRiver\ApiSdk\Model\Subscription as DrSubscription;
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

      // TODO: add more commands
      $this->info('  get-customer:    get customer by id');
      $this->info('  get-subscription:get subscription by id');
      $this->info('  get-checkout:    get checkout by id');
      $this->info('  get-order:       get order by id');
      $this->info('  get-plan:        get plan by id');
      $this->info('  get-webhook:     get webhook by id');
      $this->info('  get-invoice:     get product by id');
      $this->info('  get-refund:      get refund by id');
      $this->info('  get-event:       get event by id');
      $this->info('  try-event:       get event and invoke the handler');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'init':
        return $this->initPlan();

      case 'clear':
        return $this->clear();

      case 'enable-hook':
        return $this->enableWebhook(true);

      case 'disable-hook':
        return $this->enableWebhook(false);

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function initPlan()
  {
    // create dr plans mapping [name => plan]
    $drPlans = [];
    foreach ($this->drService->listPlan() as $drPlan) {
      $drPlans[$drPlan->getName()] = $drPlan;
    };

    // create / update default plan
    $this->info("Create or update plans ...");
    foreach (SubscriptionPlan::where('status', SubscriptionPlan::STATUS_ACTIVE)->get() as $subscriptionPlan) {
      try {
        // restore dr_plan_id from name, there must be only one active plan with the same name
        $subscriptionPlan->dr_plan_id = ($drPlans[$subscriptionPlan->name] ?? null)?->getId();
        $subscriptionPlan->save();

        $drPlan = $this->drService->updatePlan($subscriptionPlan);
        $this->info("Plan updated: {$subscriptionPlan->name} -> {$drPlan->getId()}");
      } catch (\Throwable $th) {
        $drPlan = $this->drService->createPlan($subscriptionPlan);
        $subscriptionPlan->dr_plan_id = $drPlan->getId();
        $subscriptionPlan->save();
        $this->info("Plan created: {$subscriptionPlan->name} -> {$drPlan->getId()}");
      }
    }
    $this->info("Create or update plan ... done!");

    // create default customer
    $this->info("Create default customer 'user1.test'");
    $user = User::where('name', 'user1.test')->first();
    $this->manager->createOrUpdateCustomer($user->billing_info);

    return self::SUCCESS;
  }

  public function enableWebhook(bool $enable)
  {
    // create / update hook
    $this->info('Update default webhooks ...');
    $this->manager->updateDefaultWebhook($enable);
    $this->info('Update default webhooks ... done');
  }

  public function clear()
  {
    if (config('dr.dr_mode') !== 'test' && config('dr.dr_mode') !== 'staging') {
      $this->warn('This command can not be executed under "prod" mode');
      return self::FAILURE;
    }

    /**
     * clear plans
     */
    $this->info('Clear plans ...');

    /** @var SubscriptionPlan[] $plans */
    $plans = $this->drService->planApi->listPlans(state: 'draft')->getData();
    foreach ($plans as $plan) {
      $this->info("  delete plan " . $plan->name);
      $this->ignore(
        [$this->drService->planApi, 'deletePlans'],
        $plan->name
      );
    }

    $this->info('Clear plans ...');
    $this->info('');

    /**
     * clear subscriptions
     */

    $this->info('Clear subscriptions ...');

    /** @var DrSubscription[] $subscriptions */
    $subscriptions = $this->drService->subscriptionApi->listSubscriptions(state: 'draft', limit: 50)->getData();
    foreach ($subscriptions as $subscription) {
      $checkouts = $this->drService->checkoutApi->listCheckouts(subscription_id: $subscription->getId())->getData();
      if ($checkouts && isset($checkouts[0])) {
        $this->info("  delete checkout " . $checkouts[0]->getId());
        $this->ignore(
          [$this->drService->checkoutApi, 'deleteCheckouts'],
          $checkouts[0]->getId()
        );
      }
      $this->info("  delete subscription " . $subscription->getId());
      $this->ignore(
        [$this->drService->subscriptionApi, 'deleteSubscriptions'],
        $subscription->getId()
      );
    }
    $subscriptions = $this->drService->subscriptionApi->listSubscriptions(state: 'active')->getData();
    foreach ($subscriptions as $subscription) {
      $this->info("  cancel subscription " . $subscription->getId());
      $this->ignore(
        [$this->drService->subscriptionApi, 'updateSubscriptions'],
        $subscription->getId(),
        new DrSubscription(['state' => 'cancelled'])
      );
    }

    $this->info('Clear subscriptions ...');
    $this->info('');

    /**
     * clear order
     */

    $this->info('Clear orders ...');

    /** @var DrOrder[] $orders */
    // clear acctpted order
    $orders = $this->drService->orderApi->listOrders(state: 'accepted')->getData();
    foreach ($orders as $order) {
      $this->info("  cancel draft order " . $order->getId());
      $this->drService->fulfillOrder(orderId: $order->getId(), cancel: true);
    }

    // clear fullfuled order
    // $orders = $this->drService->orderApi->listOrders(state: 'fulfilled')->getData();
    // foreach ($orders as $order) {
    //   $this->info("  cancel fulfilled order " . $order->getId());
    //   $this->drService->fulfillOrder(orderId: $order->getId(), cancel: true);
    // }

    $this->info('Clear orders ...');
    $this->info('');

    /**
     * clear customers
     */

    $this->info('Clear customers ...');

    /** @var DrCustomer[] $customers */
    $customers = $this->drService->customerApi->listCustomers()->getData();
    foreach ($customers as $customer) {
      foreach ($customer->getSources() ?? [] as $source) {
        $this->info("  delete customer source " . $source->getId());
        $this->ignore(
          [$this->drService->customerApi, 'deleteCustomerSource'],
          $customer->getId(),
          $source->getId()
        );
      };
      $this->info("  delete customer " . $customer->getId());
      $this->ignore(
        [$this->drService->customerApi, 'deleteCustomers'],
        $customer->getId()
      );
    }

    $this->info('Clear customers ...');
    $this->info('');

    return self::SUCCESS;
  }

  public function ignore($callback)
  {
    try {
      $args = func_get_args();
      array_shift($args);

      // sleep 1/20 seconds to prevent to exceed limits
      // usleep(1000000 / 20);
      return call_user_func_array($callback, $args);
    } catch (\Throwable $th) {
      $this->info($th->getMessage());
      return null;
    }
  }
}
