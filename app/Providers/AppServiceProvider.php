<?php

namespace App\Providers;

use App\Services\DigitalRiver\SubscriptionManager;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use App\Services\DigitalRiver\SubscriptionManagerMockup;
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
    if (config('dr.dr_unit_test')) {
      $this->app->bind(SubscriptionManager::class, fn () => new SubscriptionManagerMockup());
    } else {
      $this->app->bind(SubscriptionManager::class, fn () => new SubscriptionManagerDR());
    }
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
