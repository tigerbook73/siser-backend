<?php

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
    Schema::create('lds_pools', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->unique();
      $table->unsignedInteger('subscription_level');
      $table->unsignedInteger('license_count')->comment('total license count');
      $table->unsignedInteger('license_free')->comment('free licenses');
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
    Schema::dropIfExists('lds_pool');
  }
};
