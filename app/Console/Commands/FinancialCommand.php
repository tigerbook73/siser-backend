<?php

namespace App\Console\Commands;

use App\Services\DigitalRiver\FinancialService;
use Illuminate\Console\Command;

class FinancialCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'financial:cmd {subcmd=help}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Financial Command.';


  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    if (app()->environment('staging')) {
      $this->error('This command is not allowed in staging environment');
      return self::FAILURE;
    }

    $subcmd = $this->argument('subcmd');
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan financial:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  sync:            sync financial data from DR to DB (incremental)');
      $this->info('  resync:          sync financial data from DR to DB (full)');

      // TODO: next step
      $this->info('');
      $this->warn('  ------------ the following subcmd is not implemented yet ------------');
      $this->warn('  dump:            dump financial data csv file to S3');
      $this->warn('  insight:         generate insight report from financial data');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'resync':
        return $this->sync(force: true);

      case 'sync':
        return $this->sync();

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function sync(bool $force = false)
  {
    $service = new FinancialService();
    if ($force) {
      $service->resyncAll();
    } else {
      $service->syncAll();
    }
    return self::SUCCESS;
  }
}
