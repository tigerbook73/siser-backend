<?php

namespace App\Console\Commands;

use App\Models\LicenseSharingInvitation;
use App\Services\LicenseSharing\LicenseSharingService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class LicenseSharingCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'license-sharing:cmd {subcmd=help}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Cancel expired license sharing';

  public function __construct(public LicenseSharingService $service)
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
    if (!$subcmd || $subcmd == 'help') {
      $this->info('Usage: php artisan license-sharing:cmd {subcmd}');
      $this->info('');
      $this->info('subcmd:');
      $this->info('  help:            display this information');
      $this->info('  expire:          expires license sharing invitations');
      $this->info('  clean:           remove deleted license sharing invitations');
      $this->info('');

      return self::SUCCESS;
    }

    switch ($subcmd) {
      case 'expire':
        return $this->expire();

      case 'clean':
        return $this->clean();

      default:
        $this->error("Invalid subcmd: {$subcmd}");
        return self::FAILURE;
    }
  }

  public function expire()
  {
    Log::info('Artisan: license-sharing:cmd expire: start');

    /** @var LicenseSharingInvitation[]|Collection $invitations */
    $invitations = LicenseSharingInvitation::whereIn('status', [
      LicenseSharingInvitation::STATUS_ACCEPTED,
      LicenseSharingInvitation::STATUS_OPEN,
    ])
      ->where('expires_at', '<', now())
      ->get();

    foreach ($invitations as $invitation) {
      $this->service->expireLicenseSharingInvitation($invitation);
    }

    Log::info("Artisan: license-sharing:sharing:cmd expire: expires {$invitations->count()} cancelled subscriptions.");

    return Command::SUCCESS;
  }

  public function clean()
  {
    Log::info("Artisan: license-sharing:sharing:cmd clean: start");

    $count = LicenseSharingInvitation::where('status', LicenseSharingInvitation::STATUS_DELETED)
      ->where('updated_at', '<', now()->subDay())
      ->delete();

    Log::info("Artisan: license-sharing:sharing:cmd clean: delete {$count} cancelled subscriptions.");
  }
}
