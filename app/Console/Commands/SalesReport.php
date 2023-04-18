<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\DigitalRiver\SubscriptionManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SalesReport extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'sales:report {--dry-run : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'update sales records';

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
    1. if last months report not generated, generate last month's report
    2. update this month's report

    update report:
    1. fetch all remain records for specified month
    2. fetch latest report for specified month
    3. merge data from 1 & 2
    */

    // TODO: run daily

    return Command::SUCCESS;
  }
}
