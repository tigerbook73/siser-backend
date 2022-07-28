<?php

namespace App\Services\Cognito;

use App\Events\UserSubscriptionLevelChanged;

class SiserEventSubscriber
{
  /**
   * Create the event listener.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  public function handleUserSubscriptionLevelChanged(UserSubscriptionLevelChanged $event)
  {
    SiserSynchronizer::dispatch($event->user);
  }

  /**
   * Register the listeners for the subscriber.
   *
   * @param  \Illuminate\Events\Dispatcher  $events
   * @return array
   */
  public function subscribe($events)
  {
    return [
      UserSubscriptionLevelChanged::class => 'handleUserSubscriptionLevelChanged',
    ];
  }
}
