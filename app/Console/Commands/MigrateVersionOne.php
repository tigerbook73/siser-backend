<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class MigrateVersionOne extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'siser:migrate20230331';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Fix the migration of 20230331';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $lastMigration = DB::table('migrations')->orderByDesc('id')->first();
    if (
      $lastMigration->id == 35 &&
      $lastMigration->migration == '2023_03_31_000000_update_users_table' &&
      $lastMigration->batch == 7
    ) {
      DB::table('migrations')
        ->where('migration', 'like', '2023_03_31_000000%')
        ->update(['batch' => 8]);

      Artisan::call('migrate:rollback', ['--force' => true]);

      $this->info('Fix incorrect migrations 2023-03-31.');
    }


    return Command::SUCCESS;
  }
}
