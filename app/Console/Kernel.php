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
    $schedule->call(fn () => Artisan::queue('subscription:stop-cancelled'))->hourly()->name('queue subscription:stop-cancelled');
    $schedule->call(fn () => Artisan::queue('subscription:renew-annual'))->hourly()->name('queue subscription:renew-annual');
    $schedule->call(fn () => Artisan::queue('subscription:warn-pending'))->daily()->name('queue subscription:warn-pending');
    $schedule->call(fn () => Artisan::queue('subscription:log', ['subcmd' => 'statistic']))->dailyAt('00:33')->name('queue subscription:log statistic');
    $schedule->call(fn () => Artisan::queue('financial:cmd', ['subcmd' => 'sync']))->dailyAt('00:10')->name('queue financial:cmd sync');
    $schedule->call(fn () => Artisan::queue('license-sharing:cmd', ['subcmd' => 'expire']))->dailyAt('00:20')->name('queue license-sharing:cmd expire');
    $schedule->call(fn () => Artisan::queue('license-sharing:cmd', ['subcmd' => 'clean']))->dailyAt('00:20')->name('queue license-sharing:cmd clean');
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
