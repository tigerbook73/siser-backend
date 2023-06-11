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
    Schema::table('users', function (Blueprint $table) {
      $table->dropColumn('blacklisted')->default(false);

      $table->string('type')->nullable();
    });

    DB::table('users')->whereNotNull('cognito_id')->update(['type' => User::TYPE_NORMAL]);
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      $table->boolean('blacklisted')->default(false);

      $table->drop('type');
    });
  }
};
