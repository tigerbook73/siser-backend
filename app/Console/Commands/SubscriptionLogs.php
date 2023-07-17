<?php

namespace App\Console\Commands;

use App\Models\CriticalSection;
use App\Models\Subscription;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SubscriptionLogs extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:logs {id?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'retrieve subscription logs';

  public function __construct(public SubscriptionManager $manager)
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
    Log::info('Artisan: subscription:logs: start');

    $id = $this->argument('id');

    if (!$id) {
      return $this->list();
    } else {
      return $this->listLogs($id);
    }
  }

  public function list()
  {
    $maxCount = 100;

    /** @var CriticalSection[]|Collection @sections */
    $sections = CriticalSection::where('type', 'subscription')->select(['user_id', 'object_id'])->distinct()
      ->orderBy('object_id', 'desc')
      ->limit($maxCount + 1)
      ->get();

    $moreItems = false;
    if ($sections->count() > $maxCount) {
      $sections->pop();
      $moreItems = true;
    }

    if ($sections->count() <= 0) {
      Log::info('There is no subscriptions to process.');
      return Command::SUCCESS;
    }

    $title = sprintf(
      "%-10s %-10s %-20s %-10s",
      'id',
      'user_id',
      'plan',
      'status'
    );
    Log::info(" $title");
    foreach ($sections as $section) {
      /** @var Subscription $subscription */
      $subscription = Subscription::find($section->object_id);
      $text = sprintf(
        "%-10d %-10d %-20s %-10s",
        $subscription->id ?? $section->object_id,
        $subscription->user_id ?? $section->user_id,
        $subscription->plan_info['name'] ?? "",
        $subscription->status ?? "deleted"
      );
      Log::info(" $text");
    }

    if ($moreItems) {
      Log::info('There are more subscriptions to process');
    }
    return Command::SUCCESS;
  }

  public function listLogs(string|int $id)
  {
    /** @var CriticalSection[] $sections */
    $sections = CriticalSection::where('type', 'subscription')
      ->where('object_id', $id)
      ->get();

    Log::info("Subscription: $id");
    foreach ($sections as $section) {
      $text = sprintf(
        "action (%s): %s - %s",
        $section->status,
        $section->action['action'],
        $section->action['status']
      );

      if ($section->status != 'closed') {
        $this->warn($text);
      } else {
        Log::info($text);
      }
      foreach ($section->steps as $step) {
        $text = sprintf("  %s - %s", $step['time'], $step['step']);
        Log::info($text);
      }
    }
  }
}
