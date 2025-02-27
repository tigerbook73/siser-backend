<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchivePaddlePrice implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @param string[] $priceIds The IDs of the prices to archive
   *
   * @return void
   */
  public function __construct(public array $priceIds) {}

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    /**
     * @var SubscriptionManagerPaddle $manager
     */
    $manager = app(SubscriptionManagerPaddle::class);

    $manager->priceService->archivePrices($this->priceIds);
  }
}
