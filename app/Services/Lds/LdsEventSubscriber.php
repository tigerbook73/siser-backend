<?php

namespace App\Services\Lds;

use App\Events\LdsRegistered;
use App\Events\LdsUnregistered;
use App\Events\UserDeleted;
use App\Events\UserSaved;

class LdsEventSubscriber
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

  public function handleUserSaved(UserSaved $event)
  {
    LdsSynchronizer::dispatch('UserSaved', $event->user)->onConnection('sync');
  }

  public function handleUserDeleted(UserDeleted $event)
  {
    LdsSynchronizer::dispatch('UserDeleted', $event->user)->onConnection('sync');
  }

  public function handleLdsRegistered(LdsRegistered $event)
  {
    LdsSynchronizer::dispatch('LdsRegistered', $event->ldsRegistration)->onConnection('sync');
  }

  public function handleLdsUnregistered(LdsUnregistered $event)
  {
    LdsSynchronizer::dispatch('LdsUnregistered', $event->ldsRegistration)->onConnection('sync');
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
      UserSaved::class => 'handleUserSaved',
      UserDeleted::class => 'handleUserDeleted',
      LdsRegistered::class => 'handleLdsRegistered',
      LdsUnregistered::class => 'handleLdsUnregistered',
    ];
  }
}
