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
      $table->string('registration_code', 12)->comment('one to one mapping with user_id');
      $table->foreignId('user_id')->constrained();
      $table->string('device_id');
      $table->timestamp('registration');
      $table->timestamp('last_checkin')->nullable();
      $table->timestamp('expires_at')->nullable();
      $table->string('status')->comment('active|inactive');
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
    Schema::dropIfExists('lds_instances');
  }
};
