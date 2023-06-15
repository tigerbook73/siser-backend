<?php

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
          "expires_at"          : "time",
          "latest_action"      : {
            "action"    : "register|check-in|check-out",
            "client_ip" : "ip",
            "time"      : "time"
          }
        }
      ]')));

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('lds_license');
  }
};
