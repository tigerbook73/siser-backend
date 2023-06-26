<?php

namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;


class AdminResetPassword extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'admin:reset-password {password?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Reset admin@iifuture.com\'s password.';

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $this->info('This is to reset admin@iifuture.com\'s password.');

    $password = $this->argument('password');
    if (!$password) {
      $password = $this->secret('New Password (empty for random password): ');
      if (!$password) {
        $password = Str::random(12);
        $this->info('New Password: ' . $password);
      }
    }

    $admin = AdminUser::where('email', 'admin@iifuture.com')->first();
    $admin->password = Hash::make($password);
    $admin->save();

    $this->info("");
    $this->info("Password updated.");
    return Command::SUCCESS;
  }
}
