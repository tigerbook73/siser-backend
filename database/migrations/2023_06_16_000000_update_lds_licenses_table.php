<?php

use App\Models\LdsLicense;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    User::with('lds_registrations')
      ->where('subscription_level', '>=', 1)
      ->orderBy('id')
      ->chunk(
        500,
        function ($users) {
          /** @var User[] $users */
          foreach ($users as $user) {
            $LdsLicense = LdsLicense::createFromUser($user);
            $devices = [];
            foreach ($user->lds_registrations as $lds_registration) {
              if ($lds_registration->status == 'active') {
                $devices[$lds_registration->device_id] = [
                  'device_id' => $lds_registration->device_id,
                  'user_code' => $lds_registration->user_code,
                  'device_name' => $lds_registration->device_name,
                  'status'  => 'offline',
                  'expires_at' => 0,
                  'registered_at' => $lds_registration->created_at->unix(),
                  'latest_action' => [
                    'action' => 'register',
                    'client_ip' => '',
                    'time' => time(),
                  ],
                ];
              }
            }
            $LdsLicense->devices = $devices;
            $LdsLicense->save();
          }
        }
      );
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
  }
};
