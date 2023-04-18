<?php

namespace App\Console\Commands;

use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;

class SalesRecord extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'sales:record {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'fetch all sales records';

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
    $maxCount = 1000;
    $dryRun = $this->option('dry-run');

    // TODO: 
    /*
    1. get latest records from our DB
    2. fetch next 1000 records from DR
    3. transform data from DR to our DB records
    4. if there are more data, continue
    5. update sales report
    */

    return Command::SUCCESS;
  }
}
