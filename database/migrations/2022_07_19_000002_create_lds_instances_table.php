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
    Schema::create('lds_instances', function (Blueprint $table) {
      $table->id();
      $table->foreignId('lds_pool_id')->constrained();
      $table->foreignId('lds_registration_id')->unique();
      $table->foreignId('user_id');
      $table->string('device_id');
      $table->string('user_code')->comment('user registration code');
      $table->timestamp('registered_at');
      $table->boolean('online');
      $table->unsignedBigInteger('expires_at');
      $table->timestamps();

      $table->index('device_id');
      $table->index('user_code');
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
    Schema::dropIfExists('lds_instances');
  }
};
