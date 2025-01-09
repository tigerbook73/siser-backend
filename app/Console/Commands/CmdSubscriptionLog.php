<?php

namespace App\Console\Commands;

use App\Services\StatisticRecordService;
use Illuminate\Console\Command;

class CmdSubscriptionLog extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:log {subcmd=help}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'subscription log command';


  public function __construct(
    public StatisticRecordService $statisticService
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
      $this->info('Usage: php artisan subscription:log {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  generate:        rebuild history subscripton logs');
      $this->info('  statistic:       generate statistic records');
      $this->info('  reset:           reset statistic records');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'generate':
        return $this->generate();

      case 'statistic':
        return $this->statistic();

      case 'reset':
        return $this->reset();

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function generate()
  {
    // create / update default plan
    $this->info("Generating subscription logs ...");

    $result = $this->statisticService->rebuildSubscriptionLogs();

    $this->info("Generating subscription logs ... Done!");
    $this->info("   activated:  {$result['activated']}");
    $this->info("   cancelled:  {$result['cancelled']}");
    $this->info("   converted:  {$result['converted']}");
    $this->info("   extended:   {$result['extended']}");
    $this->info("   failed:     {$result['failed']}");
    $this->info("   stopped:    {$result['stopped']}");

    return self::SUCCESS;
  }

  public function statistic()
  {
    $this->info("Generating statistic records ...");

    $this->statisticService->generateRecords();

    $this->info("Generating statistic records ... Done!");
  }

  public function reset()
  {
    $this->info("Reseting statistic records ...");

    $this->statisticService->resetRecords();
    $this->statisticService->generateRecords();

    $this->info("Reseting statistic records ... Done!");
  }
}
