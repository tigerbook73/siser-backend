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
    Schema::create('lds_registrations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->string('device_id');
      $table->string('user_code')->comment('registration code return to LDS client');
      $table->string('device_name');
      $table->timestamps();

      $table->index('user_code');
      $table->index('device_id');
      $table->unique(['user_code', 'device_id']);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('lds_registrations');
  }
};
