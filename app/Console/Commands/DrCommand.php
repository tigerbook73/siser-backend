<?php

namespace App\Console\Commands;

use App\Models\GeneralConfiguration;
use App\Services\DigitalRiver\DigitalRiverService;
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
      $this->info('  help:        display this information');
      $this->info('  init:        initialize prebuild data in DigitalRiver');
      $this->info('  clear:       try to clear all test data');
      return 0;
    }

    switch ($subcmd) {
      case 'init':
        return $this->init();

      default:
        $this->error("Invalid subcmd: ${subcmd}");
        return -1;
    }
  }

  public function init()
  {
    $drService = new DigitalRiverService();

    // create / update default plan
    $this->info("Create or update default plan ...");
    if (!$defaultPlan = $drService->getDefaultPlan()) {
      $defaultPlan = $drService->createDefaultPlan(GeneralConfiguration::getConfiguration());
    } else {
      $defaultPlan = $drService->updateDefaultPlan(GeneralConfiguration::getConfiguration());
    }
    $this->info("Default Plan:");
    $this->info((string)$defaultPlan);
    $this->info("Create or update default plan ... done!");

    // create / update hook
    $this->info('Update default webhooks ...');
    $drService->updateDefaultWebhook();
    $this->info('Update default webhooks ... done');

    return 0;
  }

  public function clear()
  {
    return 0;
  }
}
