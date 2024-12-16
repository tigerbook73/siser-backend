<?php

use App\Models\LdsLicense;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
    Schema::create('lds_licenses', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->unique()->constrained();
      $table->unsignedInteger('subscription_level');
      $table->unsignedInteger('license_count')->comment('total license count');
      $table->unsignedInteger('license_free')->comment('free licenses');
      $table->unsignedInteger('license_used')->comment('used licenses');
      $table->unsignedInteger('latest_expires_at')->default(0);
      $table->unsignedInteger('lastest_expires_at')->default(0);
      $table->json('devices')->nullable()->comment(json_encode(json_decode('[
        "%device_id%" => {
          "user_code"           : "AAAABBBBCCCCDDDD",
          "device_id"           : "ABCDEFG123",
          "device_name"         : "my device",
          "status"              : "online|offline",
          "registered_at"       : 0,
          "expires_at"          : 0,
          "latest_action"      : {
            "action"    : "register|check-in|check-out",
            "client_ip" : "ip",
            "time"      : 0
          }
        }
      ]')));

      $table->timestamps();
    });

    User::with('lds_registrations')
      ->where('subscription_level', '>=', 1)
      ->orderBy('id')
      ->chunk(500, function ($users) {
        $ldsRecords = [];

        /** @var User[] $users */
        foreach ($users as $user) {
          $ldsLicense = new LdsLicense();
          $ldsLicense->user_id            = $user->id;
          $ldsLicense->subscription_level = $user->subscription_level;
          $ldsLicense->license_count      = 0;
          $ldsLicense->license_free       = 0;
          $ldsLicense->license_used       = 0;
          $ldsLicense->latest_expires_at  = 0;
          $ldsLicense->lastest_expires_at = 0;
          $ldsLicense->devices            = [];

          $devices = [];
          foreach ($user->lds_registrations as $lds_registration) {
            if ($lds_registration->status != 'active') {
              continue;
            }
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
          $ldsLicense->devices = $devices;
          $ldsLicense->created_at = now();
          $ldsLicense->updated_at = now();
          $ldsRecords[] = $ldsLicense->getAttributes();
        }
        LdsLicense::insert($ldsRecords);
      });
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
