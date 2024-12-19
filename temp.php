<?php

use App\Models\Subscription;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

function abc()
{
  Subscription::where('status', Subscription::STATUS_ACTIVE)
    ->whereNotNull('meta->paddle->subscription_id')
    // ->where('discount', '<>', 0)
    ->whereNull('coupon_info')
    ->chunkById(10, function ($subscriptions) {
      foreach ($subscriptions as $subscription) {
        $output = new Symfony\Component\Console\Output\BufferedOutput();

        Artisan::call(
          'paddle:cmd',
          [
            'subcmd' => 'update-subscription',
            '--subscription' => $subscription->id,
          ],
          $output
        );
        $outputContent = $output->fetch();

        printf("cmd output: {$outputContent}");
        printf(".");
      }
      printf("\n");
      $this->info("Updated {$subscriptions->count()} subscriptions, sleeping for 1 seconds...");
      sleep(1);
    });
}
