<?php

namespace App\Console\Commands;

use App\Models\CriticalSection;
use App\Notifications\SubscriptionWarning;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;

class SubscriptionWarnUnclosed extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:warn-unclosed {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Warn unclosed critical sections';

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
    $dryRun = $this->option('dry-run');

    $ids = CriticalSection::unclosed()
      ->map(fn ($model) => $model->id)
      ->all();

    $this->info('There are ' . count($ids) . ' unclosed critical section: [' . implode(', ', $ids) . '] !');

    if (!$dryRun && count($ids) > 0) {
      SubscriptionWarning::notify(SubscriptionWarning::NOTIF_UNCLOSED_CRITICAL_SECTION, $ids);
      CriticalSection::whereIn('id', $ids)->update(['need_notify' => false]);
    }

    return Command::SUCCESS;
  }
}
