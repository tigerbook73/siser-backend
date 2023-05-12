<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    // $schedule->command('inspire')->hourly();

    // $schedule->command('auth:clear-resets')->everyFifteenMinutes();

    $schedule->call(fn () => Artisan::queue('subscription:clean-draft'))->cron('*/30 * * * *');
    $schedule->call(fn () => Artisan::queue('subscription:stop-cancelled'))->cron('*/29 * * * *');
    $schedule->call(fn () => Artisan::queue('subscription:warn-pending'))->cron('* */2 * * *');
    $schedule->call(fn () => Artisan::queue('subscription:warn-unclosed'))->cron('*/57 * * * *');
  }

  /**
   * Register the commands for the application.
   *
   * @return void
   */
  protected function commands()
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
