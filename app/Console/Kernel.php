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

    $schedule->call(fn () => Artisan::queue('subscription:clean-draft'))->everyThirtyMinutes()->name('queue subscription:clean-draft');
    $schedule->call(fn () => Artisan::queue('subscription:stop-cancelled'))->everyThirtyMinutes()->name('queue subscription:stop-cancelled');
    $schedule->call(fn () => Artisan::queue('subscription:warn-pending'))->everyTwoHours()->name('queue subscription:warn-pending');
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
