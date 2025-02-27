<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Services\Paddle\SubscriptionManagerPaddle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizePaddlePrice implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @param ?int $planId The ID of the plan to synchronize, if null then synchronize all plans
   *
   * @return void
   */
  public function __construct(public ?int $planId = null) {}

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    /** @var SubscriptionManagerPaddle $manager */
    $manager = app(SubscriptionManagerPaddle::class);

    if ($this->planId) {
      $plan = Plan::findById($this->planId);
      $manager->priceService->syncPlan($plan);
    } else {
      foreach (Plan::public()->get() as $plan) {
        $manager->priceService->syncPlan($plan);
      }
    }
  }
}
