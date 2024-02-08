<?php

namespace App\Providers;

use App\Events\SubscriptionOrderEvent;
use App\Services\FirstPromoter\FirstPromoterEventListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
  /**
   * The event to listener mappings for the application.
   *
   * @var array<class-string, array<int, class-string>>
   */
  protected $listen = [
    // 
  ];


  /**
   * The subscriber classes to register.
   *
   * @var array
   */
  protected $subscribe = [
    \App\Services\Cognito\SiserEventSubscriber::class,
  ];

  /**
   * Register any events for your application.
   *
   * @return void
   */
  public function boot()
  {
    //
    if (config('affiliate.enabled')) {
      Event::listen(SubscriptionOrderEvent::class, FirstPromoterEventListener::class);
    }
  }

  /**
   * Determine if events and listeners should be automatically discovered.
   *
   * @return bool
   */
  public function shouldDiscoverEvents()
  {
    return false;
  }
}
