<?php

namespace App\Console\Commands;

use App\Models\DrEventRecord;
use App\Models\Invoice;
use App\Services\DigitalRiver\DigitalRiverService;
use App\Services\DigitalRiver\SubscriptionManagerDR;
use DigitalRiver\ApiSdk\Model\Order as DrOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SubscriptionWarnPending extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'dr:event {subcmd=help} {event-type?} {event-id?} {--dry-run=true : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'dr event related commands.';


  /**
   * member variable
   */
  protected $dryRun = false;

  public function __construct(public SubscriptionManagerDR $manager)
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
    $subcmd = $this->argument('subcmd');
    $this->dryRun = $this->option('dry-run') === 'false' ? false : true;

    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan dr:event {subcmd} {event-type?} {event-id?} {--dry-run}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:                        display this information');
      $this->info('  event-details:               print details of an event');
      $this->info('     event-id:                 event id to print details');
      $this->info('  list-failed:                 list all faild and not resolved events');
      $this->info('     event-type:               event type to list, if not specified, list all');
      $this->info('  list-processing:             list all processing events');
      $this->info('     event-type:               event type to list, if not specified, list all');
      $this->info('  order.complete:              try to resolve order.complete events');
      $this->info('  subscription.extended:       try to resolve subscription.extended events');
      $this->info('  subscription.reminder:       try to resolve subscription.reminder events');
      $this->info('  all-events:                  try to resolve all events');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'event-details':
        $this->printEventDetails();
        return self::SUCCESS;

      case 'list-failed':
        $this->listFailedEvents();
        return self::SUCCESS;

      case 'list-processing':
        $this->listProcessingEvents();
        return self::SUCCESS;

      case 'order.complete':
        $this->resolveOrderComplete();
        return self::SUCCESS;

      case 'subscription.extended':
        $this->resolveSubscriptionExtended();
        return self::SUCCESS;

      case 'subscription.reminder':
        $this->resolveSubscriptionReminder();
        return self::SUCCESS;

      case 'all-event':
        $this->resolveAllEvents();
        return self::SUCCESS;

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function listTable(Collection $events, array $keys)
  {
    $events = $events->toArray();
    $this->table($keys, array_map(fn($event) => array_map(fn($key) => $event[$key], $keys), $events));
  }

  public function listFailedEvents()
  {
    $type = $this->argument('event-type');

    $query = DrEventRecord::where('status', DrEventRecord::STATUS_FAILED)
      ->where('resolve_status', DrEventRecord::RESOLVE_STATUS_UNRESOLVED);
    if ($type) {
      $query->where('type', $type);
    }
    $events = $query->get();

    // display
    $this->listTable($events, [
      'id',
      'event_id',
      'type',
      'user_id',
      'subscription_id',
      'status',
      'resolve_status',
      'created_at'
    ]);
  }

  public function listProcessingEvents()
  {
    $type = $this->argument('event-type');

    $query = DrEventRecord::where('status', DrEventRecord::STATUS_PROCESSING)
      ->where('resolve_status', DrEventRecord::RESOLVE_STATUS_UNRESOLVED);
    if ($type) {
      $query->where('type', $type);
    }
    $events = $query->get();

    // display
    $this->listTable($events, [
      'id',
      'event_id',
      'type',
      'user_id',
      'subscription_id',
      'status',
      'resolve_status',
      'created_at'
    ]);
  }

  public function printEventDetails()
  {
    $eventId = $this->argument('event-type');

    /**
     * @var DrEventRecord $event
     */
    $event = DrEventRecord::where('event_id', $eventId)->first();
    if (!$event) {
      $this->error("Event not found: {$eventId}");
      return;
    }

    printf("\n-------------------------------------------------------------------------------\n");
    printf("%-20s: %s\n", 'ID', $event->id);
    printf("%-20s: %s\n", 'Event ID', $event->event_id);
    printf("%-20s: %s\n", 'Type', $event->type);
    printf("%-20s: %s\n", 'User ID', $event->user_id);
    printf("%-20s: %s\n", 'Subscription ID', $event->subscription_id);

    printf("%-20s:\n", 'Data');
    foreach ($event->data as $key => $value) {
      printf("  %-18s: %s\n", $key, $value);
    }

    printf("%-20s:\n", 'Messages');
    foreach ($event->messages as $message) {
      printf("  %s\n", $message);
    }

    printf("%-20s: %s\n", 'Status', $event->status);
    printf("%-20s: %s\n", 'Resolve Status', $event->resolve_status);
    printf("%-20s: %s\n", 'Created At', $event->created_at);
    printf("-------------------------------------------------------------------------------\n\n");
  }

  /**
   * try to resolve all events
   */
  public function resolveAllEvents()
  {
    $this->resolveOrderComplete();
    $this->resolveSubscriptionExtended();
    $this->resolveSubscriptionReminder();
  }

  /**
   * try to resolve order.complete events
   */

  public function resolveOrderComplete()
  {
    $this->resolveOrderCompleteFulfilled();
  }


  /**
   * try to resolve order.completed while state is fulfilled
   */
  public function resolveOrderCompleteFulfilled()
  {
    /** @var DrEventRecord[] $events */
    $events = DrEventRecord::where('type', 'order.complete')
      ->where('status', DrEventRecord::STATUS_FAILED)
      ->where('resolve_status', DrEventRecord::RESOLVE_STATUS_UNRESOLVED)
      ->get();

    foreach ($events as $event) {
      $drEvent = $this->manager->drService->getEvent($event->event_id);
      if ($drEvent->getData()->getObject()['state'] !== DrOrder::STATE_FULFILLED) {
        continue;
      }

      $drOrder = $this->manager->drService->getOrder($drEvent->getData()->getObject()['id']);
      $invoice = Invoice::findByDrOrderId($drEvent->getData()->getObject()['id']);
      if ($drOrder->getState() === DrOrder::STATE_FULFILLED) {
        // order is still fulfilled
        printf("order.completed is fullfilled and order is fulfilled: %s\n", $event->event_id);
      } else if ($drOrder->getState() === DrOrder::STATE_COMPLETE) { {
          if ($invoice->isCompleted()) {
            // order is completed and invoice are processed
            printf("order.completed is fullfilled and order is completed and invoice is completed: %s\n", $event->event_id);
            if (!$this->dryRun) {
              $event->resolve('order.completed in fulfilled state');
            }
          } else {
            // order is completed and invoice are not processed
            printf("order.completed is fullfilled and order is completed but invoice is not processed: %s\n", $event->event_id);
          }
        }
      }
    }
  }

  /**
   * try to resolve subscription.extended events
   */
  public function resolveSubscriptionExtended()
  {
    $this->resolveSubscriptionExtendedWithDuplication();
  }


  /**
   * try to resolve duplicated subscription.extended (with different event id but same content)
   */
  public function resolveSubscriptionExtendedWithDuplication()
  {
    /** @var DrEventRecord[] $events */
    $events = DrEventRecord::where('type', 'subscription.extended')
      ->where('status', DrEventRecord::STATUS_FAILED)
      ->where('resolve_status', DrEventRecord::RESOLVE_STATUS_UNRESOLVED)
      ->get();

    foreach ($events as $event) {
      $drEvent = $this->manager->drService->getEvent($event->event_id);
      $invoice = Invoice::findByDrInvoiceId($drEvent->getData()->getObject()['invoice']->id);
      if (!$invoice) {
        printf("invoice not found: %s\n", $drEvent->getData()->getObject()['invoice']->id);
        continue;
      }

      $anotherEvent = DrEventRecord::where('event_id', '<>', $event->event_id)
        ->where('type', 'subscription.extended')
        ->where('subscription_id', $invoice->subscription_id)
        ->where('status', DrEventRecord::STATUS_COMPLETED)
        ->where('created_at', '<', $event->created_at->addDays(1))
        ->orderBy('id', 'desc')
        ->first();

      if ($anotherEvent) {
        $drEventAnother = $this->manager->drService->getEvent($anotherEvent->event_id);

        // event and another event are the same (invoice id is same)
        if (
          $drEventAnother->getData()->getObject()['invoice']->id ==
          $drEvent->getData()->getObject()['invoice']->id
        ) {
          printf("event: %s, another event: %s\n", $event->event_id, $anotherEvent->event_id);
          if (!$this->dryRun) {
            $event->resolve('subscription.extended with different id but same content');
          }
        }
      }
    }
  }

  /**
   * try to resolve subscription.reminder events
   */
  public function resolveSubscriptionReminder()
  {
    $this->resolveSubscriptionReminderWithDuplication();
  }

  /**
   * try to resolve duplicated subscription.reminder (with different event id but same content)
   */
  public function resolveSubscriptionReminderWithDuplication()
  {

    /**
     * duplicated subscription.reminder (with different event id but same content)
     */

    /**
     * @var DigitalRiverService $service
     */
    $service = app(DigitalRiverService::class);

    /** @var DrEventRecord[] $events */
    $events = DrEventRecord::where('type', 'subscription.reminder')
      ->where('status', DrEventRecord::STATUS_FAILED)
      ->where('resolve_status', DrEventRecord::RESOLVE_STATUS_UNRESOLVED)
      ->get();

    foreach ($events as $event) {
      $drEvent = $service->getEvent($event->event_id);
      $invoice = Invoice::findByDrInvoiceId($drEvent->getData()->getObject()['invoice']->id);
      if (!$invoice) {
        printf("invoice not found: %s\n", $drEvent->getData()->getObject()['invoice']->id);
        continue;
      }

      $anotherEvent = DrEventRecord::where('event_id', '<>', $event->event_id)
        ->where('type', 'subscription.reminder')
        ->where('subscription_id', $invoice->subscription_id)
        ->where('status', DrEventRecord::STATUS_COMPLETED)
        ->where('created_at', '<', $event->created_at->addDays(1))
        ->orderBy('id', 'desc')
        ->first();

      if ($anotherEvent) {
        $drEventAnother = $service->getEvent($anotherEvent->event_id);

        // event and another event are the same (invoice id is same)
        if (
          $drEventAnother->getData()->getObject()['invoice']->id ==
          $drEvent->getData()->getObject()['invoice']->id
        ) {
          printf("event: %s, another event: %s\n", $event->event_id, $anotherEvent->event_id);
          if (!$this->dryRun) {
            $event->resolve('subscription.reminder with different id but same content');
          }
        }
      }
    }
  }
}
