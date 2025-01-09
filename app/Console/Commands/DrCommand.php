<?php

namespace App\Console\Commands;

use App\Services\DigitalRiver\SubscriptionManager;
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
    $subcmd = $this->argument('subcmd');
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan dr:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  enable-hook:     enable webhook');
      $this->info('  disable-hook:    disable webhook');
      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'enable-hook':
        return $this->enableWebhook(true);

      case 'disable-hook':
        return $this->enableWebhook(false);

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function enableWebhook(bool $enable)
  {
    // create / update hook
    $this->info('Update default webhooks ...');
    $this->manager->updateDefaultWebhook($enable);
    $this->info('Update default webhooks ... done');
  }
}
