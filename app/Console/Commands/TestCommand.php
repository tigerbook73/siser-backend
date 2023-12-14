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
    return self::SUCCESS;
  }
}
