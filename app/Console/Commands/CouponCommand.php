<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Console\Command;

class CouponCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'coupon:cmd {subcmd=help}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'coupon related commands.';

  /**
   * constructor
   */
  public function __construct(private SubscriptionManagerPaddle $manager)
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
    $subcmd = $this->argument('subcmd');
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan coupon:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  generate:        generate coupon codes in batch');
      $this->info('');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'generate':
        return $this->generate();

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function generate()
  {
    $this->info('Generating coupons ...');
    $this->info('');

    $couponData = [];

    // default data
    $coupon_number    = 10;
    $code_pattern     = "########";

    $coupon_event     = "";
    $product_name     = "Leonardo® Design Studio Pro";
    $type             = Coupon::TYPE_ONCE_OFF;
    $discount_type    = Coupon::DISCOUNT_TYPE_PERCENTAGE;
    $percentage_off   = 20;
    $interval         = Coupon::INTERVAL_MONTH;
    $interval_count   = 1;
    $start_date       = date('Y-m-d');
    $end_date         = date('Y-m-d', strtotime('+1 year'));
    $status           = Coupon::STATUS_ACTIVE;

    // collect data
    while (true) {
      while (true) {
        $coupon_number = (int)$this->ask('Number of Coupons to Generate (1~5000)', (string)$coupon_number);
        if ($coupon_number >= 1 && $coupon_number <= 5000) {
          break;
        }
        $this->error("Invalid number of coupons: {$coupon_number}");
      }
      while (true) {
        $code_pattern = strtoupper(trim($this->ask('Code Pattern (e.g. ABC######, len: 5~16)', $code_pattern)));
        if (
          strlen($code_pattern) >= 5 &&
          strlen($code_pattern) <= 16 &&
          pow(26, substr_count($code_pattern, '#')) >= $coupon_number
        ) {
          break;
        }
        $this->error("Invalid code pattern: {$code_pattern}");
      }
      while (true) {
        $coupon_event = trim($this->ask('Coupon Event (e.g. HSN)'));
        if ($coupon_event && strlen($coupon_event) > 2) {
          break;
        }
        $this->error('Coupon event is required and should be at least 3 characters long.');
      }
      $product_name = $this->choice('Product Name', ['Leonardo® Design Studio Pro'], $product_name);
      $type = $this->choice('Type', [Coupon::TYPE_SHARED, Coupon::TYPE_ONCE_OFF], $type);
      $discount_type = $this->choice('Discount Type', [Coupon::DISCOUNT_TYPE_PERCENTAGE, Coupon::DISCOUNT_TYPE_FREE_TRIAL], $discount_type);
      while (true) {
        if ($discount_type == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
          $percentage_off = 100;
          break;
        }
        $percentage_off = (int)$this->ask('Percentage Off (1~99)', (string)$percentage_off);
        if ($percentage_off >= 1 && $percentage_off <= 99) {
          break;
        }
        $this->error("Invalid percentage off: {$percentage_off}");
      }
      if ($discount_type == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
        $interval = $this->choice('Interval', [Coupon::INTERVAL_DAY, Coupon::INTERVAL_MONTH, Coupon::INTERVAL_YEAR], $interval);
      } else {
        $interval = $this->choice('Interval', [Coupon::INTERVAL_DAY, Coupon::INTERVAL_MONTH, Coupon::INTERVAL_YEAR, Coupon::INTERVAL_LONGTERM], $interval);
      }

      if ($interval == Coupon::INTERVAL_LONGTERM) {
        $interval_count = 0;
      }
      while ($interval != Coupon::INTERVAL_LONGTERM) {
        $interval_count = (int)$this->ask('Interval Count (1 ~ 30)', (string)$interval_count);
        if ($interval_count >= 1 && $interval_count <= 30) {
          break;
        }
        $this->error("Invalid interval count: {$interval_count}");
      }
      $start_date = $this->ask('Start Date (YYYY-MM-DD)', $start_date);
      $end_date = $this->ask('End Date (YYYY-MM-DD)', $end_date);
      $status = $this->choice('Status', [Coupon::STATUS_ACTIVE, Coupon::STATUS_DRAFT], $status);

      $couponData = [
        'coupon_event'      => $coupon_event,
        'product_name'      => $product_name,
        'type'              => $type,
        'discount_type'     => $discount_type,
        'name'              => "", // see below
        'percentage_off'    => $percentage_off,
        'interval'          => $interval,
        'interval_count'    => $interval_count,
        'start_date'        => $start_date,
        'end_date'          => $end_date,
        'status'            => $status,
      ];

      // update coupon name
      if ($couponData['discount_type'] == Coupon::DISCOUNT_TYPE_FREE_TRIAL) {
        $couponData['name'] = "{$couponData['interval_count']}-{$couponData['interval']} Free Trial";
      } else {
        if ($couponData['interval_count'] == 0) {
          $couponData['name'] = "{$couponData['percentage_off']}% off";
        } else if ($couponData['interval_count'] == 1) {
          $couponData['name'] = "{$couponData['percentage_off']}% off for 1 {$couponData['interval']}";
        } else {
          $couponData['name'] = "{$couponData['percentage_off']}% off for {$couponData['interval_count']} {$couponData['interval']}s";
        }
      }

      $this->info('Please confirm the following information');
      $this->info('');
      $this->info('Coupon number:     ' . $coupon_number);
      $this->info('Code pattern:      ' . $code_pattern);
      $this->info('Code Details:      ');
      foreach ($couponData as $key => $value) {
        $this->info(sprintf("    %-20s:     %s", $key, $value));
      }

      if ($this->confirm('Is the above data correct?', true)) {
        break;
      }
    }

    // generate codes according to patterns
    $this->info('Generating codes ...');

    $charSet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charSetSize = strlen($charSet);
    $codeSize = strlen($code_pattern);

    $couponCodes = [];
    while (count($couponCodes) < $coupon_number) {
      $code = '';
      for ($i = 0; $i < $codeSize; $i++) {
        if ($code_pattern[$i] != '#') {
          $code .= $code_pattern[$i];
          continue;
        }
        $code .= $charSet[rand(0, $charSetSize - 1)];
      }
      if (in_array($code, $couponCodes) || Coupon::where('code', $code)->exists()) {
        continue;
      }
      $couponCodes[] = $code;
    }

    // display codes
    $this->info('Generating codes ... Done! Following are some of the codes:');
    for ($i = 0; $i < count($couponCodes) && $i < 20; $i++) {
      $this->info('  ' . $couponCodes[$i]);
    }
    if ($i < count($couponCodes)) {
      $this->info('  ...');
    }

    // final confirm
    if (!$this->confirm('Create coupons with the above codes?', false)) {
      $this->info('Aborted!');
      return self::SUCCESS;
    }

    // create coupons
    $this->withProgressBar($couponCodes, function ($code) use ($couponData) {
      $coupon = new Coupon($couponData);
      $coupon->code = $code;
      $coupon->save();

      $this->manager->discountService->createOrUpdatePaddleDiscount($coupon);
    });

    $this->info('');
    $this->info('');
    $this->info('Generating coupons ... Done!');
    $this->info('');
  }
}
