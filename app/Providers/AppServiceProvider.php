<?php

namespace App\Providers;

use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    if ($this->app->environment('local')) {
      $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
      $this->app->register(TelescopeServiceProvider::class);
    }

    // subscription manager
    $this->app->bind(SubscriptionManager::class, SubscriptionManagerDR::class);
  }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
    //
  }
}
