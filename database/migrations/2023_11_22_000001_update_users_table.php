<?php

use App\Models\Base\User;
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
    Schema::table('users', function (Blueprint $table) {
      $table->unsignedInteger('machine_count')->default(0)->after('subscription_level');
    });

    User::chunkById(100, function ($users) {
      foreach ($users as $user) {
        $user->machine_count = $user->machines()->count();
        $user->save();
      }
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
