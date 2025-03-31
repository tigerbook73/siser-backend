<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'test:cmd';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

  public function __construct()
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
    /**
     * only for local environment
     */
    if (!app()->environment('local')) {
      $this->error('This command is only available in local environment');
      return self::FAILURE;
    }

    $this->info('This is a test command');
    return self::SUCCESS;
  }
}
