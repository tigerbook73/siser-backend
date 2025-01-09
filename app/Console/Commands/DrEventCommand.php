<?php

namespace App\Console\Commands;

use App\Models\DrEventRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class DrEventCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'webhook:event {subcmd=help} {event-type?} {event-id?} {--dry-run=true : Dry run}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'webhook event related commands.';


  /**
   * member variable
   */
  protected $dryRun = false;

  public function __construct()
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
      $this->info('  help:                          display this information');
      $this->info('  details {event_id?}:           print details of an event');
      $this->info('  list-failed {event_type?}:     list all faild and not resolved events');
      $this->info('  list-processing {event_type?}: list all processing events');
      $this->info('  resolve {event_id}:            resolve an event by force');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'details':
        $this->printEventDetails();
        return self::SUCCESS;

      case 'list-failed':
        $this->listFailedEvents();
        return self::SUCCESS;

      case 'list-processing':
        $this->listProcessingEvents();
        return self::SUCCESS;

      case 'resolve':
        $this->resolveByForce();
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
    $events = $query->orderBy('id', 'desc')->get();

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
    $events = $query->orderBy('id', 'desc')->get();

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
     * @var ?DrEventRecord $event
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

  public function resolveByForce()
  {
    $eventId = $this->argument('event-id');

    /**
     * @var ?DrEventRecord $event
     */
    $event = DrEventRecord::where('event_id', $eventId)->first();
    if (!$event) {
      $this->warn("Event not found: {$eventId}");
      return;
    }

    if (
      $event->status != DrEventRecord::STATUS_FAILED ||
      $event->resolve_status == DrEventRecord::RESOLVE_STATUS_RESOLVED
    ) {
      $this->warn("Event is not failed or already resolved: {$eventId}");
      return;
    }

    $this->info("Resolving event: {$eventId}");

    // resolve
    $event->resolve('Resolved by force');

    $this->info("Event resolved: {$eventId}");
  }
}

function resolveFailedByForce()
{
  /**
   * @var DrEventRecord[] $events
   */
  $events = DrEventRecord::where('status', DrEventRecord::STATUS_FAILED)
    ->where('resolve_status', DrEventRecord::RESOLVE_STATUS_UNRESOLVED)
    ->get();

  foreach ($events as $event) {
    $event->resolve('Resolved by force');
  }
}
